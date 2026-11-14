<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\Service;
use App\Models\Setting;
use App\Services\Agenda;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GuestBookingController extends Controller
{
    public function __construct(
        private Agenda $agenda,
        private AppointmentService $appointments,
    ) {}

    public function index(Request $request)
    {
        $services = Service::active()->ordered()->get();
        $hairdressers = Hairdresser::active()->orderBy('name')->get();
        $mpPublicKey = config('services.mercadopago.public_key');
        $settings = Setting::all_values();

        // Próximos dias em que a barbearia abre (para o seletor de datas)
        $days = collect(range(0, (int) $settings['booking_days_ahead']))
            ->map(fn ($i) => now()->startOfDay()->addDays($i))
            ->filter(fn (Carbon $d) => $this->agenda->isOpenOn($d))
            ->values()
            ->map(fn (Carbon $d) => [
                'date' => $d->format('Y-m-d'),
                'weekday' => $d->isToday() ? 'Hoje' : ($d->isTomorrow() ? 'Amanhã' : ucfirst($d->translatedFormat('D'))),
                'day' => $d->format('d'),
                'month' => ucfirst($d->translatedFormat('M')),
            ]);

        return view('booking.index', [
            'services' => $services,
            'hairdressers' => $hairdressers,
            'mpPublicKey' => $mpPublicKey,
            'paymentsEnabled' => ! empty(config('services.mercadopago.access_token')),
            'allowPayLater' => (bool) $settings['allow_pay_later'],
            'days' => $days,
            'preselectedService' => $request->integer('servico') ?: null,
            'preselectedHairdresser' => $request->integer('profissional') ?: null,
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $service = isset($data['service_id']) ? Service::find($data['service_id']) : Service::orderBy('duration_minutes')->first();
        if (! $service) {
            return response()->json([]);
        }

        return response()->json($this->agenda->slots(
            Carbon::parse($data['date'], config('app.timezone')),
            Hairdresser::findOrFail($data['hairdresser_id']),
            $service,
        ));
    }

    public function process(Request $request)
    {
        try {
            $data = $request->validate([
                'client_name' => 'required|string|min:2|max:255',
                'client_phone' => ['required', 'string', 'max:20', function ($attr, $value, $fail) {
                    $digits = strlen(preg_replace('/\D/', '', $value));
                    if ($digits < 10 || $digits > 13) {
                        $fail('Informe um WhatsApp válido com DDD.');
                    }
                }],
                'email' => 'nullable|email|max:255',
                'service_id' => 'required|exists:services,id',
                'hairdresser_id' => 'required|exists:hairdressers,id',
                'scheduled_at' => 'required|date_format:Y-m-d H:i:s',
                'payment_method' => 'required|in:pix,credit_card,pay_later',
                'token' => 'nullable|string',
                'payment_method_id' => 'nullable|string|max:40',
                'card_cpf' => 'required_if:payment_method,credit_card|nullable|string|size:11',
                'notes' => 'nullable|string|max:500',
            ], [
                'scheduled_at.required' => 'Escolha a data e o horário.',
                'card_cpf.required_if' => 'Informe o CPF do titular do cartão.',
                'card_cpf.size' => 'O CPF deve ter 11 dígitos.',
            ]);

            $service = Service::findOrFail($data['service_id']);
            $hairdresser = Hairdresser::findOrFail($data['hairdresser_id']);
            $start = Carbon::parse($data['scheduled_at'], config('app.timezone'));
            $paymentsEnabled = ! empty(config('services.mercadopago.access_token'));

            if ($data['payment_method'] === 'pay_later' && ! Setting::get('allow_pay_later') && $paymentsEnabled) {
                throw ValidationException::withMessages(['payment_method' => 'Escolha PIX ou cartão para garantir o horário.']);
            }
            if (! $paymentsEnabled && $data['payment_method'] !== 'pay_later') {
                throw ValidationException::withMessages(['payment_method' => 'Pagamento online indisponível no momento. Escolha "Pagar na barbearia".']);
            }

            // Trava para evitar dois clientes pegando o mesmo horário ao mesmo tempo
            $lock = Cache::lock("booking:{$hairdresser->id}:{$start->format('YmdHi')}", 20);
            if (! $lock->get()) {
                return $this->fail('Este horário acabou de ser reservado. Escolha outro.', 409);
            }

            try {
                if (! $this->agenda->isAvailable($start, $hairdresser, $service)) {
                    return $this->fail('Este horário não está mais disponível. Escolha outro horário.', 409);
                }

                $customer = $this->appointments->findOrCreateCustomer($data['client_name'], $data['client_phone'], $data['email'] ?? null);

                $appointment = new Appointment([
                    'client_name' => $data['client_name'],
                    'client_phone' => $customer->phone,
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'hairdresser_id' => $hairdresser->id,
                    'scheduled_at' => $start,
                    'total_price' => $service->price,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'pending',
                    'status' => $data['payment_method'] === 'pay_later' ? 'scheduled' : 'pending_payment',
                    'notes' => $data['notes'] ?? null,
                ]);

                $paymentInfo = [];
                if ($data['payment_method'] !== 'pay_later') {
                    $result = $this->chargeMercadoPago($request, $data, $service, $customer->email);
                    if (isset($result['error'])) {
                        return $this->fail($result['error'], 400);
                    }
                    $appointment->fill($result['attributes']);
                    $paymentInfo = $result['info'];
                }

                $appointment->save();
            } finally {
                $lock->release();
            }

            if ($appointment->payment_status === 'paid') {
                $this->appointments->markPaid($appointment);
            }
            if ($appointment->status === 'scheduled') {
                $this->appointments->notify($appointment, 'confirmation');
            }

            return response()->json([
                'success' => true,
                'message' => 'Agendamento realizado!',
                'redirect' => route('booking.show', $appointment->token),
                'payment_info' => $paymentInfo,
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first(), 422);
        } catch (\Throwable $e) {
            Log::error('Erro no agendamento: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->fail('Não foi possível concluir o agendamento agora. Tente novamente ou fale conosco pelo WhatsApp.', 500);
        }
    }

    /** Página do agendamento do cliente (confirmação, PIX, cancelamento). */
    public function show(string $token)
    {
        $appointment = Appointment::with(['service', 'hairdresser', 'customer'])->where('token', $token)->firstOrFail();

        return view('booking.success', [
            'appointment' => $appointment,
            'loyaltyTarget' => (int) Setting::get('loyalty_target', 10),
        ]);
    }

    /** Consulta usada pela página para saber se o PIX já foi pago. */
    public function status(string $token)
    {
        $appointment = Appointment::where('token', $token)->firstOrFail();

        // Se o webhook não chegou, consulta o Mercado Pago diretamente
        if ($appointment->payment_status !== 'paid' && $appointment->payment_id && config('services.mercadopago.access_token')) {
            try {
                \MercadoPago\MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
                $payment = (new \MercadoPago\Client\Payment\PaymentClient())->get((int) $appointment->payment_id);
                if (($payment->status ?? null) === 'approved') {
                    $this->appointments->markPaid($appointment->load('service', 'customer'));
                    $this->appointments->notify($appointment, 'confirmation');
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao consultar pagamento: '.$e->getMessage());
            }
        }

        return response()->json([
            'payment_status' => $appointment->fresh()->payment_status,
            'status' => $appointment->fresh()->status,
        ]);
    }

    public function cancel(string $token)
    {
        $appointment = Appointment::where('token', $token)->firstOrFail();

        if (! $appointment->canBeCancelledByClient()) {
            return back()->with('error', 'Este agendamento não pode mais ser cancelado pelo site. Fale conosco pelo WhatsApp.');
        }

        $this->appointments->cancel($appointment);

        return back()->with('status', 'Agendamento cancelado. Esperamos ver você em breve!');
    }

    /** Link antigo (por ID) — redireciona para o novo link seguro. */
    public function legacySuccess($id)
    {
        $appointment = Appointment::findOrFail($id);
        abort_if($appointment->created_at->lt(now()->subHour()), 404);

        return redirect()->route('booking.show', $appointment->token);
    }

    private function chargeMercadoPago(Request $request, array $data, Service $service, ?string $email): array
    {
        \MercadoPago\MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
        $client = new \MercadoPago\Client\Payment\PaymentClient();

        $payload = [
            'transaction_amount' => (float) $service->price,
            'description' => Setting::get('shop_name').': '.$service->name,
            'notification_url' => app()->environment('production') ? url('/webhooks/mercadopago') : null,
            'payer' => [
                'email' => $email ?: $request->input('email') ?: 'cliente@'.parse_url(config('app.url'), PHP_URL_HOST),
                'first_name' => $data['client_name'],
            ],
        ];
        $payload = array_filter($payload, fn ($v) => $v !== null);

        try {
            if ($data['payment_method'] === 'pix') {
                $payload['payment_method_id'] = 'pix';
                $payment = $client->create($payload);
                $tx = $payment->point_of_interaction->transaction_data ?? null;

                return [
                    'attributes' => [
                        'payment_id' => (string) $payment->id,
                        'pix_qr_code' => $tx->qr_code_base64 ?? null,
                        'pix_copy_paste' => $tx->qr_code ?? null,
                    ],
                    'info' => ['payment_id' => $payment->id],
                ];
            }

            if (empty($data['token'])) {
                return ['error' => 'Não conseguimos validar o cartão. Confira os dados e tente novamente.'];
            }

            $payload['token'] = $data['token'];
            $payload['installments'] = 1;
            $payload['payment_method_id'] = $data['payment_method_id'] ?? null;
            $payload['payer']['identification'] = ['type' => 'CPF', 'number' => $data['card_cpf']];
            $payment = $client->create($payload);

            if ($payment->status === 'rejected') {
                return ['error' => $this->rejectionMessage($payment->status_detail ?? '')];
            }

            return [
                'attributes' => [
                    'payment_id' => (string) $payment->id,
                    'payment_status' => $payment->status === 'approved' ? 'paid' : 'pending',
                    'status' => $payment->status === 'approved' ? 'scheduled' : 'pending_payment',
                ],
                'info' => ['payment_id' => $payment->id, 'status' => $payment->status],
            ];
        } catch (\Throwable $e) {
            $details = method_exists($e, 'getApiResponse') && $e->getApiResponse()
                ? json_encode($e->getApiResponse()->getContent())
                : '';
            Log::error('Erro Mercado Pago: '.$e->getMessage().' | '.$details);

            return ['error' => 'O pagamento não foi aprovado pela operadora. Tente outro método ou escolha pagar na barbearia.'];
        }
    }

    private function rejectionMessage(string $detail): string
    {
        return match ($detail) {
            'cc_rejected_bad_filled_card_number' => 'Número do cartão inválido.',
            'cc_rejected_bad_filled_date' => 'Data de validade incorreta.',
            'cc_rejected_bad_filled_security_code' => 'CVV inválido.',
            'cc_rejected_insufficient_amount' => 'Saldo/limite insuficiente.',
            'cc_rejected_high_risk' => 'Pagamento recusado pela análise de segurança. Tente PIX.',
            'cc_rejected_call_for_authorize' => 'Seu banco pediu autorização. Ligue para o banco ou use PIX.',
            'cc_rejected_max_attempts' => 'Limite de tentativas excedido. Tente outro cartão ou PIX.',
            default => 'Pagamento recusado pelo emissor do cartão. Tente outro cartão ou PIX.',
        };
    }

    private function fail(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}

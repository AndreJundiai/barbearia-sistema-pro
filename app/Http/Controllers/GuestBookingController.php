<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GuestBookingController extends Controller
{
    public function index()
    {
        try {
            $services = Service::all();
            $hairdressers = \App\Models\Hairdresser::all();
            $mpPublicKey = env('MERCADO_PAGO_PUBLIC_KEY');
            return view('booking.index', compact('services', 'hairdressers', 'mpPublicKey'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao carregar agendamento: " . $e->getMessage());
            if (config('app.debug')) {
                throw $e;
            }
            return response("Erro no Agendamento: " . $e->getMessage() . " (Verifique se o banco de dados está migrado e se as chaves do Mercado Pago em .env estão corretas)", 500);
        }
    }

    public function process(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Iniciando processamento de agendamento", ['payload' => $request->all()]);
            \Illuminate\Support\Facades\Log::info("Validando agendamento para: " . $request->input('scheduled_at'));

            $data = $request->validate([
                'client_name' => 'required|string|max:255',
                'client_phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'service_id' => 'required|exists:services,id',
                'hairdresser_id' => 'required|exists:hairdressers,id',
                'scheduled_at' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        try {
                            $scheduled = \Carbon\Carbon::parse($value, config('app.timezone'));
                            $now = \Carbon\Carbon::now(config('app.timezone'));
                            
                            // Se for uma data do passado real (ex: ontem)
                            if ($scheduled->isBefore($now->startOfDay())) {
                                $fail('A data do agendamento não pode ser no passado.');
                                return;
                            }
                            
                            // Se for hoje, mas um horário que já passou há mais de 10 min
                            if ($scheduled->isSameDay($now) && $scheduled->isBefore($now->copy()->subMinutes(10))) {
                                $fail('O horário selecionado (' . $scheduled->format('H:i') . ') já passou. Escolha um horário futuro.');
                            }

                        } catch (\Exception $e) {
                            $fail('Formato de data e hora inválido.');
                        }
                    }
                ],
                'payment_method' => 'required|in:pix,credit_card,pay_later',
                'token' => 'nullable|string',
                // Campos do cartão (opcionais na validação principal, checados manualmente abaixo se for cartão)
                'card_name' => 'required_if:payment_method,credit_card|string|nullable',
                'card_cpf' => 'required_if:payment_method,credit_card|string|size:11|nullable',
                'card_number' => 'nullable|string|max:19',
                'card_expiry' => 'nullable|string|max:5',
                'card_cvv' => 'nullable|string|max:4',
            ], [
                'scheduled_at.required' => 'Por favor, informe a data e hora do agendamento.',
            ]);

            // Verificar disponibilidade (bloqueio de horário)
            $exists = Appointment::where('hairdresser_id', $data['hairdresser_id'])
                ->where('scheduled_at', $data['scheduled_at'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Desculpe, este profissional já foi agendado para este exato horário.'
                ], 422);
            }

            $service = Service::find($data['service_id']);
            
            // Configuração Mercado Pago
            $accessToken = env('MERCADO_PAGO_ACCESS_TOKEN');
            $isMercadoPagoEnabled = !empty($accessToken);

            // Gestão Automatizada de Cliente
            $customer = Customer::where('phone', $data['client_phone'])->first();
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $data['client_name'],
                    'phone' => $data['client_phone'],
                    'email' => $data['email'] ?? null,
                ]);
            } else {
                if (!$customer->email && isset($data['email'])) {
                    $customer->update(['email' => $data['email']]);
                }
            }

            $data['total_price'] = $service->price;
            $data['customer_id'] = $customer->id;
            $paymentInfo = [];

            if ($isMercadoPagoEnabled && $data['payment_method'] !== 'pay_later') {
                try {
                    if (!class_exists('\MercadoPago\MercadoPagoConfig')) {
                        throw new \Exception("SDK do Mercado Pago não instalado. Execute 'composer update'.");
                    }

                    \MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
                    $client = new \MercadoPago\Client\Payment\PaymentClient();

                    $createRequest = [
                        "transaction_amount" => (float) $service->price,
                        "description" => "Agendamento: " . $service->name,
                        "payer" => [
                            "email" => $request->input('email') ?: 'cliente@exemplo.com',
                            "first_name" => $request->input('client_name'),
                            "identification" => [
                                "type" => "CPF",
                                "number" => $request->input('card_cpf') ?: '00000000000'
                            ]
                        ]
                    ];

                    if ($data['payment_method'] === 'pix') {
                        $createRequest["payment_method_id"] = "pix";
                        $payment = $client->create($createRequest);
                        
                        if (isset($payment->status) && $payment->status === 'pending') {
                            $data['payment_status'] = 'pending';
                            $data['status'] = 'pending_payment';
                            $data['payment_id'] = (string) $payment->id;
                            
                            $qrCodeBase64 = $payment->point_of_interaction->transaction_data->qr_code_base64 ?? '';
                            $qrCode = $payment->point_of_interaction->transaction_data->qr_code ?? '';

                            $paymentInfo = [
                                'payment_id' => $payment->id,
                                'qr_code' => $qrCode,
                                'qr_code_base64' => $qrCodeBase64,
                                'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? ''
                            ];
                        } else {
                            throw new \Exception("Erro ao gerar PIX: " . ($payment->status ?? 'desconhecido'));
                        }
                    } elseif ($data['payment_method'] === 'credit_card') {
                        $cardToken = $request->input('token');
                        
                        // O Token DEVE vir do frontend (SDK V2)
                        if (!$cardToken) {
                             throw new \Exception("Ocorreu um erro na validação do cartão. Por favor, tente novamente.");
                        }

                        $createRequest["token"] = $cardToken;
                        $createRequest["installments"] = 1;
                        $createRequest["payment_method_id"] = $request->input('payment_method_id'); // Enviado pelo SDK V2
                        
                        $payment = $client->create($createRequest);
                        
                        if (isset($payment->status)) {
                            $data['payment_status'] = $payment->status === 'approved' ? 'paid' : 'pending';
                            $data['status'] = $payment->status === 'approved' ? 'scheduled' : 'pending_payment';
                            $data['payment_id'] = (string) $payment->id;
                            
                            $paymentInfo = [
                                'payment_id' => $payment->id,
                                'status' => $payment->status,
                                'status_detail' => $payment->status_detail ?? ''
                            ];

                            if ($payment->status === 'rejected') {
                                $motivo = 'Pagamento recusado.';
                                if ($payment->status_detail === 'cc_rejected_bad_filled_card_number') $motivo = 'Número do cartão inválido.';
                                elseif ($payment->status_detail === 'cc_rejected_bad_filled_date') $motivo = 'Data de validade incorreta.';
                                elseif ($payment->status_detail === 'cc_rejected_bad_filled_security_code') $motivo = 'CVV inválido.';
                                elseif ($payment->status_detail === 'cc_rejected_insufficient_amount') $motivo = 'Saldo insuficiente.';
                                elseif ($payment->status_detail === 'cc_rejected_other_reason') $motivo = 'Recusado pelo emissor do cartão.';
                                elseif ($payment->status_detail === 'cc_rejected_high_risk') $motivo = 'Recusado por segurança (antifraude).';
                                elseif ($payment->status_detail === 'cc_rejected_call_for_authorize') $motivo = 'Requer autorização do banco.';
                                elseif ($payment->status_detail === 'cc_rejected_max_attempts') $motivo = 'Máximo de tentativas excedido.';
                                
                                return response()->json([
                                    'success' => false,
                                    'message' => $motivo . ' (' . $payment->status_detail . ')'
                                ], 400);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $details = "";
                    $userMessage = "Ocorreu um erro no processamento do Mercado Pago.";
                    
                    if (method_exists($e, 'getApiResponse') && $e->getApiResponse()) {
                        $content = $e->getApiResponse()->getContent();
                        $details = json_encode($content);
                        
                        if (isset($content['message'])) {
                            if ($content['message'] === 'Invalid users involved') {
                                $userMessage = "E-mail de cliente inválido ou você está tentando fazer um auto-pagamento.";
                            } else {
                                $userMessage = "Erro na operadora: " . $content['message'];
                            }
                        }
                    }
                    
                    \Illuminate\Support\Facades\Log::error("Erro Mercado Pago: " . $e->getMessage() . " | Detalhes da API: " . $details);
                    
                    return response()->json([
                        'success' => false,
                        'message' => $userMessage
                    ], 400);
                }
            } else {
                $data['payment_status'] = $data['payment_method'] === 'pay_later' ? 'pending' : 'paid';
                $data['status'] = 'scheduled';
            }


            $appointment = Appointment::create($data);

            // Notificação
            try {
                $customer->notify(new \App\Notifications\AppointmentNotification($appointment, 'confirmation'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erro Notificação: " . $e->getMessage());
            }

            // Registrar no financeiro
            if ($appointment->payment_status === 'paid') {
                $customer->increment('total_spent', $appointment->total_price);
                $customer->increment('loyalty_points', 1);
                
                try {
                    Transaction::create([
                        'type' => 'income',
                        'amount' => $appointment->total_price,
                        'description' => "Agendamento Online (" . $data['payment_method'] . "): " . $customer->name . " - " . $service->name,
                        'appointment_id' => $appointment->id,
                        'transaction_date' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Erro financeiro: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Agendamento realizado!',
                'appointment_id' => $appointment->id,
                'payment_info' => $paymentInfo
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ERRO GENERALIZADO NO AGENDAMENTO: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function success($id)
    {
        $appointment = Appointment::with('service')->findOrFail($id);
        return view('booking.success', compact('appointment'));
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->query('date');
        $hairdresserId = $request->query('hairdresser_id');

        if (!$date || !$hairdresserId) {
            return response()->json([]);
        }

        $busySlots = Appointment::where('hairdresser_id', $hairdresserId)
            ->whereDate('scheduled_at', $date)
            ->pluck('scheduled_at')
            ->map(function ($dt) {
                return \Carbon\Carbon::parse($dt)->format('H:i');
            });

        return response()->json($busySlots);
    }
}

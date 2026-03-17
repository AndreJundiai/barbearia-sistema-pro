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
            return view('booking.index', compact('services', 'hairdressers'));
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }
            return response("Erro no Agendamento: " . $e->getMessage() . " (Verifique se o banco de dados está migrado e populado)", 500);
        }
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'service_id' => 'required|exists:services,id',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'scheduled_at' => 'required|date|after:now',
            'payment_method' => 'required|in:pix,credit_card,pay_later',
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
        $appointmentId = null;
        $paymentInfo = [];

        if ($isMercadoPagoEnabled && $data['payment_method'] !== 'pay_later') {
            try {
                \MercadoPago\SDK::setAccessToken($accessToken);

                $payment = new \MercadoPago\Payment();
                $payment->transaction_amount = (float) $service->price;
                $payment->description = $service->name;
                $payment->payment_method_id = $data['payment_method']; // pix ou credit_card (ajustado pelo frontend)
                $payment->payer = [
                    "email" => $customer->email ?? 'cliente@exemplo.com',
                    "first_name" => $customer->name,
                ];

                if ($data['payment_method'] === 'pix') {
                    $payment->save();
                    
                    if ($payment->status === 'pending') {
                        $data['payment_status'] = 'pending';
                        $data['status'] = 'pending_payment';
                        $paymentInfo = [
                            'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
                            'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
                            'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url,
                            'payment_id' => $payment->id
                        ];
                    }
                } else {
                    // Para Cartão de Crédito, o payload exigiria o token do cartão via Bricks/SDK JS
                    // Por enquanto, manteremos como pendente se não houver token, ou processar normal se houver
                    $data['payment_status'] = 'pending';
                    $data['status'] = 'pending_payment';
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erro Mercado Pago: " . $e->getMessage());
                // Fallback para simulação se falhar a API real mas chaves existirem? 
                // Melhor avisar o usuário.
                return response()->json(['success' => false, 'message' => 'Erro ao processar pagamento com Mercado Pago.'], 500);
            }
        } else {
            // Simulação ou Pagamento Local
            $data['payment_status'] = $data['payment_method'] === 'pay_later' ? 'pending' : 'paid';
            $data['status'] = 'scheduled';
        }

        $appointment = Appointment::create($data);

        // Notificação
        try {
            $customer->notify(new \App\Notifications\AppointmentNotification($appointment, 'confirmation'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao enviar notificação: " . $e->getMessage());
        }

        // Registrar no financeiro se já for pago (ex: pay_later simulado como pago ou cartão aprovado)
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

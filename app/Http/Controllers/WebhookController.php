<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleMercadoPago(Request $request)
    {
        $payload = $request->all();
        \Illuminate\Support\Facades\Log::info('Mercado Pago Webhook Received:', $payload);

        if (isset($payload['type']) && $payload['type'] === 'payment') {
            $paymentId = $payload['data']['id'] ?? null;
            
            if ($paymentId) {
                try {
                    $accessToken = env('MERCADO_PAGO_ACCESS_TOKEN');
                    \MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
                    
                    $client = new \MercadoPago\Client\Payment\PaymentClient();
                    $payment = $client->get($paymentId);

                    if ($payment && $payment->status === 'approved') {
                        $appointment = Appointment::where('payment_id', (string)$paymentId)->first();
                        
                        if ($appointment && $appointment->payment_status !== 'paid') {
                            $appointment->update([
                                'payment_status' => 'paid',
                                'status' => 'scheduled'
                            ]);

                            // Incrementar gastos e pontos do cliente
                            $customer = $appointment->customer;
                            if ($customer) {
                                $customer->increment('total_spent', $appointment->total_price);
                                $customer->increment('loyalty_points', 1);
                            }

                            // Registrar transação se não existir
                            if (!Transaction::where('appointment_id', $appointment->id)->exists()) {
                                Transaction::create([
                                    'type' => 'income',
                                    'amount' => $appointment->total_price,
                                    'description' => "Agendamento Pago (Webhook): " . ($customer->name ?? 'Cliente') . " - " . ($appointment->service->name ?? 'Serviço'),
                                    'appointment_id' => $appointment->id,
                                    'transaction_date' => now(),
                                ]);
                            }

                            \Illuminate\Support\Facades\Log::info("Pagamento aprovado via Webhook: Appointment ID {$appointment->id}");
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Erro no Webhook Mercado Pago: " . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

}

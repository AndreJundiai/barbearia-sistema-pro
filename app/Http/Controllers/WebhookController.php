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
        
        // Em produção, valide a assinatura aqui usando o client_secret
        // Log::info('Mercado Pago Webhook Received:', $payload);

        if (isset($payload['type']) && $payload['type'] === 'payment') {
            $paymentId = $payload['data']['id'] ?? null;
            
            if ($paymentId) {
                // Aqui você chamaria a API do Mercado Pago para pegar os detalhes do pagamento
                // e atualizar o status do agendamento vinculado.
                
                // Exemplo simplificado de busca e atualização:
                // $appointment = Appointment::where('payment_external_id', $paymentId)->first();
                // if ($appointment) {
                //     $appointment->update(['payment_status' => 'paid']);
                // }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

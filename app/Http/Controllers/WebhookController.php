<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleMercadoPago(Request $request, AppointmentService $appointments)
    {
        $type = $request->input('type') ?? $request->input('topic');
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if ($type !== 'payment' || ! $paymentId || ! config('services.mercadopago.access_token')) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            // Nunca confiamos no corpo do webhook: buscamos o pagamento na API do Mercado Pago
            \MercadoPago\MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
            $payment = (new \MercadoPago\Client\Payment\PaymentClient())->get((int) $paymentId);

            $appointment = Appointment::with(['service', 'customer', 'hairdresser'])
                ->where('payment_id', (string) $paymentId)->first();

            if ($appointment && ($payment->status ?? null) === 'approved' && $appointment->payment_status !== 'paid') {
                $appointments->markPaid($appointment);
                $appointments->notify($appointment, 'confirmation');
                Log::info("Pagamento aprovado via webhook: agendamento #{$appointment->id}");
            }
        } catch (\Throwable $e) {
            Log::error('Erro no webhook Mercado Pago: '.$e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}

<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Transaction;
use App\Notifications\AppointmentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Regras de negócio do ciclo de vida de um agendamento:
 * cliente, pagamento, conclusão (fidelidade) e cancelamento.
 */
class AppointmentService
{
    public function findOrCreateCustomer(string $name, ?string $phone, ?string $email = null): Customer
    {
        $normalized = Customer::normalizePhone($phone);

        $customer = $normalized
            ? Customer::where('phone', $normalized)->orWhere('phone', $phone)->first()
            : null;

        if (! $customer) {
            return Customer::create(['name' => $name, 'phone' => $normalized, 'email' => $email ?: null]);
        }

        if ($email && ! $customer->email) {
            $customer->update(['email' => $email]);
        }

        return $customer;
    }

    /** Marca como pago, registra a receita uma única vez e soma no histórico do cliente. */
    public function markPaid(Appointment $appointment, ?string $method = null): void
    {
        if ($appointment->payment_status === 'paid') {
            return;
        }

        DB::transaction(function () use ($appointment, $method) {
            $appointment->update(array_filter([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'status' => $appointment->status === 'pending_payment' ? 'scheduled' : $appointment->status,
            ]));

            if (! Transaction::where('appointment_id', $appointment->id)->where('type', 'income')->exists()) {
                Transaction::create([
                    'type' => 'income',
                    'amount' => $appointment->total_price,
                    'description' => sprintf('%s — %s (%s)',
                        $appointment->service->name ?? 'Serviço',
                        $appointment->client_name,
                        $appointment->paymentLabel()),
                    'appointment_id' => $appointment->id,
                    'transaction_date' => now(),
                ]);
            }

            $appointment->customer?->increment('total_spent', (float) $appointment->total_price);
        });
    }

    /** Atendimento realizado: recebe (se ainda não pago) e soma 1 ponto de fidelidade. */
    public function complete(Appointment $appointment): void
    {
        if ($appointment->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($appointment) {
            $this->markPaid($appointment, $appointment->payment_method === 'pay_later' ? 'presencial' : null);
            $appointment->update(['status' => 'completed', 'completed_at' => now()]);
            $appointment->customer?->increment('loyalty_points');
        });
    }

    public function cancel(Appointment $appointment, string $status = 'cancelled'): void
    {
        $appointment->update(['status' => $status, 'cancelled_at' => now()]);
    }

    public function notify(Appointment $appointment, string $type = 'confirmation'): void
    {
        try {
            $appointment->loadMissing(['service', 'hairdresser', 'customer']);
            $appointment->customer?->notify(new AppointmentNotification($appointment, $type));
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar notificação: '.$e->getMessage());
        }
    }
}

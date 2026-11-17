<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

/** Cancela agendamentos cujo PIX não foi pago dentro do prazo de reserva. */
class ExpirePendingPayments extends Command
{
    protected $signature = 'app:expire-pending-payments';
    protected $description = 'Cancela agendamentos com PIX não pago após o prazo de reserva';

    public function handle(): int
    {
        $count = Appointment::expiredPayment()->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $this->info("{$count} agendamento(s) com PIX expirado cancelado(s).");

        return self::SUCCESS;
    }
}

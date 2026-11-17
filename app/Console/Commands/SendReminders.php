<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Envia lembretes para agendamentos que começam nos próximos 30-60 minutos';

    public function handle(AppointmentService $service): int
    {
        $appointments = Appointment::with(['customer', 'service', 'hairdresser'])
            ->where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('scheduled_at', [now(), now()->addMinutes(60)])
            ->get();

        foreach ($appointments as $appointment) {
            $service->notify($appointment, 'reminder');
            $appointment->update(['reminder_sent' => true]);
            $this->info("Lembrete enviado: {$appointment->client_name}");
        }

        $this->info($appointments->count().' lembrete(s) processado(s).');

        return self::SUCCESS;
    }
}

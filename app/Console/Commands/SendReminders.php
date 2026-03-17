<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Envia lembretes para agendamentos que ocorrerão em 30 minutos';

    public function handle()
    {
        $now = Carbon::now();
        $targetTime = $now->copy()->addMinutes(30);
        
        // Buscar agendamentos que começam entre 25 e 35 minutos a partir de agora
        // para dar uma margem caso o scheduler não rode exatamente no segundo
        $appointments = Appointment::with(['customer', 'service', 'hairdresser'])
            ->where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('scheduled_at', [
                $targetTime->copy()->subMinutes(5),
                $targetTime->copy()->addMinutes(5)
            ])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("Nenhum lembrete para enviar agora.");
            return;
        }

        foreach ($appointments as $appointment) {
            if ($appointment->customer) {
                try {
                    $appointment->customer->notify(new AppointmentNotification($appointment, 'reminder'));
                    $appointment->update(['reminder_sent' => true]);
                    $this->info("Lembrete enviado para: {$appointment->client_name}");
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar lembrete para agendamento #{$appointment->id}: " . $e->getMessage());
                }
            }
        }
    }
}

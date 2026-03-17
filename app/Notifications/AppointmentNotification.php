<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AppointmentNotification extends Notification
{
    use Queueable;

    protected $appointment;
    protected $type; // 'confirmation' or 'reminder'

    public function __construct(Appointment $appointment, $type = 'confirmation')
    {
        $this->appointment = $appointment;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        $channels = [];
        
        // E-mail if provided
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        // Twilio SMS/WhatsApp (Trial)
        $channels[] = 'database'; // Log in DB
        
        // Trigger manual HTTP call to Twilio on save
        $this->sendTwilioNotification($notifiable);
        
        return $channels;
    }

    protected function sendTwilioNotification($notifiable)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $isWhatsapp = config('services.twilio.whatsapp', false);

        if (!$sid || !$token || !$from) {
            Log::warning("Twilio não configurado. Pulando envio automático.");
            return;
        }

        $to = $notifiable->phone;
        $message = $this->type === 'confirmation'
            ? "Barbearia Elite: Seu agendamento para {$this->appointment->service->name} foi confirmado para " . \Carbon\Carbon::parse($this->appointment->scheduled_at)->format('d/m H:i') . "."
            : "Lembrete Elite: Seu horário de {$this->appointment->service->name} é em 30 minutos! Nos vemos lá?";

        if ($isWhatsapp) {
            $from = "whatsapp:" . $from;
            $to = "whatsapp:" . $to;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                Log::error("Erro Twilio: " . $response->body());
            } else {
                Log::info("Twilio enviado com sucesso para {$to}");
            }
        } catch (\Exception $e) {
            Log::error("Falha fatal Twilio: " . $e->getMessage());
        }
    }

    public function toMail($notifiable)
    {
        $subject = $this->type === 'confirmation' 
            ? 'Confirmação de Agendamento - Barbearia Elite' 
            : 'Lembrete de Agendamento - Barbearia Elite';

        $greeting = "Olá, {$notifiable->name}!";
        $message = $this->type === 'confirmation'
            ? "Seu agendamento para o serviço {$this->appointment->service->name} foi confirmado."
            : "Lembrete: Seu horário na Barbearia Elite é em 30 minutos!";

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($message)
                    ->line("Profissional: {$this->appointment->hairdresser->name}")
                    ->line("Data/Hora: " . \Carbon\Carbon::parse($this->appointment->scheduled_at)->format('d/m/Y H:i'))
                    ->action('Ver no Mapa', url('/'))
                    ->line('Agradecemos a preferência!');
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'type' => $this->type,
            'message' => 'Notificação enviada via canais configurados.',
        ];
    }
}

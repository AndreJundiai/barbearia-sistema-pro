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
        $channels = ['database'];

        // E-mail somente se o cliente informou e o envio de e-mail está configurado
        if ($notifiable->email && config('mail.default') !== 'log') {
            $channels[] = 'mail';
        }

        // SMS/WhatsApp via Twilio (se configurado)
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

        $to = preg_replace('/\D/', '', (string) $notifiable->phone);
        if (! $to) {
            return;
        }
        if (strlen($to) <= 11) {
            $to = '55'.$to;
        }
        $to = '+'.$to;
        $shop = \App\Models\Setting::get('shop_name');
        $message = $this->type === 'confirmation'
            ? "{$shop}: Seu agendamento para {$this->appointment->service->name} foi confirmado para " . \Carbon\Carbon::parse($this->appointment->scheduled_at)->format('d/m H:i') . "."
            : "{$shop}: lembrete! Seu horário de {$this->appointment->service->name} é às " . $this->appointment->scheduled_at->format('H:i') . ". Gerencie: " . route('booking.show', $this->appointment->token);

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
        $shop = \App\Models\Setting::get('shop_name');
        $subject = $this->type === 'confirmation'
            ? "Agendamento confirmado - {$shop}"
            : "Lembrete do seu horário - {$shop}";

        $greeting = "Olá, {$notifiable->name}!";
        $message = $this->type === 'confirmation'
            ? "Seu agendamento para o serviço {$this->appointment->service->name} foi confirmado."
            : "Lembrete: seu horário na {$shop} está chegando!";

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($message)
                    ->line("Profissional: {$this->appointment->hairdresser->name}")
                    ->line("Data/Hora: " . \Carbon\Carbon::parse($this->appointment->scheduled_at)->format('d/m/Y H:i'))
                    ->action('Ver meu agendamento', route('booking.show', $this->appointment->token))
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

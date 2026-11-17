<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    public const STATUS_LABELS = [
        'scheduled' => 'Confirmado',
        'pending_payment' => 'Aguardando pagamento',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado',
        'no_show' => 'Não compareceu',
    ];

    public const PAYMENT_LABELS = [
        'pix' => 'PIX',
        'credit_card' => 'Cartão',
        'pay_later' => 'Na barbearia',
        'presencial' => 'Na barbearia',
    ];

    protected $fillable = [
        'user_id', 'client_name', 'client_phone', 'service_id', 'hairdresser_id',
        'scheduled_at', 'status', 'total_price', 'payment_method', 'payment_status', 'commission_status',
        'reminder_sent', 'customer_id', 'pix_qr_code', 'pix_copy_paste', 'payment_id',
        'token', 'notes', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent' => 'boolean',
            'total_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            $appointment->token ??= Str::random(40);
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function hairdresser() { return $this->belongsTo(Hairdresser::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    /** Minutos que um horário fica reservado aguardando o PIX. */
    public const PAYMENT_HOLD_MINUTES = 30;

    /** Agendamentos que ocupam a agenda: não cancelados e sem PIX expirado. */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(fn ($q) => $q->where('status', '!=', 'pending_payment')
                ->orWhere('created_at', '>=', now()->subMinutes(self::PAYMENT_HOLD_MINUTES)));
    }

    public function scopeExpiredPayment($query)
    {
        return $query->where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(self::PAYMENT_HOLD_MINUTES));
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->service->duration_minutes ?? 30);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_method] ?? '—';
    }

    public function canBeCancelledByClient(): bool
    {
        $hours = (int) Setting::get('cancel_notice_hours', 2);

        return in_array($this->status, ['scheduled', 'pending_payment'])
            && $this->scheduled_at->isAfter(now()->addHours($hours));
    }

    public function whatsappLink(?string $message = null): ?string
    {
        $phone = preg_replace('/\D/', '', (string) $this->client_phone);
        if (! $phone) {
            return null;
        }
        if (strlen($phone) <= 11) {
            $phone = '55'.$phone;
        }

        return 'https://wa.me/'.$phone.($message ? '?text='.rawurlencode($message) : '');
    }

    public function googleCalendarLink(): string
    {
        $shop = Setting::get('shop_name');
        $start = $this->scheduled_at->copy()->utc()->format('Ymd\THis\Z');
        $end = $this->endsAt()->utc()->format('Ymd\THis\Z');

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => ($this->service->name ?? 'Horário').' — '.$shop,
            'dates' => $start.'/'.$end,
            'details' => 'Profissional: '.($this->hairdresser->name ?? '-'),
            'location' => trim(Setting::get('address').' '.Setting::get('city')),
        ]);
    }
}

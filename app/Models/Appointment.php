<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 'client_name', 'client_phone', 'service_id', 'hairdresser_id',
        'scheduled_at', 'status', 'total_price', 'payment_method', 'payment_status', 'commission_status',
        'reminder_sent', 'customer_id', 'pix_qr_code', 'pix_copy_paste', 'payment_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function hairdresser() { return $this->belongsTo(Hairdresser::class); }
}

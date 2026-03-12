<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hairdresser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'commission_percent',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}

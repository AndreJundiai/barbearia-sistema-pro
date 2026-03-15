<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'total_spent'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}

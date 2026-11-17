<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hairdresser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'specialty',
        'bio',
        'commission_percent',
        'active',
        'instagram',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}

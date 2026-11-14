<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Configurações da barbearia editáveis pelo painel (nome, WhatsApp, horários...).
 * Os valores padrão ficam em config/barbearia.php.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected const CACHE_KEY = 'barbearia.settings';

    protected static ?array $memo = null;

    public static function all_values(): array
    {
        return static::$memo ??= static::loadValues();
    }

    protected static function loadValues(): array
    {
        $defaults = config('barbearia', []);

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, function () {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            $stored = [];
        }

        return array_merge($defaults, array_filter($stored, fn ($v) => $v !== null));
    }

    public static function get(string $key, $default = null)
    {
        return static::all_values()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
        static::$memo = null;
    }

    /** Ex.: "Seg a Sáb" ou "Seg, Qua, Sex" */
    public static function openDaysLabel(): string
    {
        $names = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $closed = array_filter(array_map('trim', explode(',', (string) static::get('closed_days', ''))), 'strlen');
        $open = array_values(array_filter([1, 2, 3, 4, 5, 6, 0], fn ($d) => ! in_array((string) $d, $closed, true)));

        if (count($open) === 7) {
            return 'Todos os dias';
        }
        if (count($open) === 0) {
            return 'Fechado';
        }

        // Sequência contínua (na ordem Seg..Dom)
        $order = [1, 2, 3, 4, 5, 6, 0];
        $idx = array_map(fn ($d) => array_search($d, $order, true), $open);
        $isRange = count($open) > 2 && max($idx) - min($idx) === count($open) - 1;

        return $isRange
            ? $names[$open[0]].' a '.$names[end($open)]
            : implode(', ', array_map(fn ($d) => $names[$d], $open));
    }

    public static function whatsappUrl(?string $message = null): ?string
    {
        $number = preg_replace('/\D/', '', (string) static::get('whatsapp'));
        if (! $number) {
            return null;
        }
        if (strlen($number) <= 11) {
            $number = '55'.$number;
        }

        return 'https://wa.me/'.$number.($message ? '?text='.rawurlencode($message) : '');
    }

    public static function mapsUrl(): ?string
    {
        $q = trim(static::get('address').' '.static::get('city'));

        return $q ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($q) : null;
    }
}

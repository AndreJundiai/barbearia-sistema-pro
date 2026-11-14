<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit', [
            'settings' => Setting::all_values(),
            'mpConfigured' => (bool) config('services.mercadopago.access_token'),
            'twilioConfigured' => (bool) config('services.twilio.sid'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name' => 'required|string|max:80',
            'slogan' => 'nullable|string|max:120',
            'about' => 'nullable|string|max:1000',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:60',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'lunch_start' => 'nullable|date_format:H:i',
            'lunch_end' => 'nullable|date_format:H:i|after:lunch_start',
            'slot_interval' => 'required|integer|min:5|max:120',
            'closed_days' => 'nullable|array',
            'closed_days.*' => 'integer|between:0,6',
            'booking_days_ahead' => 'required|integer|min:1|max:120',
            'min_notice_minutes' => 'required|integer|min:0|max:1440',
            'cancel_notice_hours' => 'required|integer|min:0|max:72',
            'loyalty_target' => 'required|integer|min:2|max:50',
        ]);

        $data['whatsapp'] = preg_replace('/\D/', '', (string) ($data['whatsapp'] ?? ''));
        $data['instagram'] = ltrim(trim((string) ($data['instagram'] ?? '')), '@');
        $data['closed_days'] = implode(',', $data['closed_days'] ?? []);
        $data['allow_pay_later'] = $request->boolean('allow_pay_later') ? '1' : '0';
        // Campos opcionais vazios precisam ser gravados para "apagar" o valor
        foreach (['lunch_start', 'lunch_end', 'slogan', 'about', 'address', 'city'] as $key) {
            $data[$key] = $data[$key] ?? '';
        }

        Setting::put($data);

        return back()->with('status', 'Configurações salvas! O site já foi atualizado.');
    }
}

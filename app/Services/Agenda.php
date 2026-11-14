<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Calcula os horários disponíveis de cada profissional respeitando
 * expediente, almoço, dias fechados, duração do serviço e agendamentos existentes.
 */
class Agenda
{
    public function isOpenOn(Carbon $date): bool
    {
        $closed = array_filter(array_map('trim', explode(',', (string) Setting::get('closed_days', ''))), 'strlen');

        return ! in_array((string) $date->dayOfWeek, $closed, true);
    }

    /** @return array<int, array{time:string, available:bool}> */
    public function slots(Carbon $date, Hairdresser $hairdresser, Service $service, ?int $ignoreAppointmentId = null): array
    {
        if (! $this->isOpenOn($date)) {
            return [];
        }

        $tz = config('app.timezone');
        $day = $date->copy()->setTimezone($tz)->startOfDay();
        $interval = max(5, (int) Setting::get('slot_interval', 30));
        $duration = max(5, (int) $service->duration_minutes);

        $open = $this->at($day, Setting::get('open_time', '09:00'));
        $close = $this->at($day, Setting::get('close_time', '19:00'));
        $lunchStart = Setting::get('lunch_start') ? $this->at($day, Setting::get('lunch_start')) : null;
        $lunchEnd = Setting::get('lunch_end') ? $this->at($day, Setting::get('lunch_end')) : null;
        $earliest = now($tz)->addMinutes((int) Setting::get('min_notice_minutes', 0));

        $busy = Appointment::with('service')
            ->active()
            ->where('hairdresser_id', $hairdresser->id)
            ->whereBetween('scheduled_at', [$day, $day->copy()->endOfDay()])
            ->when($ignoreAppointmentId, fn ($q) => $q->where('id', '!=', $ignoreAppointmentId))
            ->get()
            ->map(fn (Appointment $a) => [$a->scheduled_at->copy()->setTimezone($tz), $a->endsAt()->setTimezone($tz)]);

        $slots = [];
        foreach (CarbonPeriod::create($open, "{$interval} minutes", $close) as $start) {
            $start = Carbon::instance($start);
            $end = $start->copy()->addMinutes($duration);

            if ($end->gt($close)) {
                break;
            }
            if ($lunchStart && $lunchEnd && $start->lt($lunchEnd) && $end->gt($lunchStart)) {
                continue; // horário de almoço não aparece
            }

            $available = $start->gte($earliest)
                && $busy->every(fn ($b) => ! ($start->lt($b[1]) && $end->gt($b[0])));

            $slots[] = ['time' => $start->format('H:i'), 'available' => $available];
        }

        return $slots;
    }

    public function isAvailable(Carbon $start, Hairdresser $hairdresser, Service $service, ?int $ignoreAppointmentId = null): bool
    {
        return collect($this->slots($start, $hairdresser, $service, $ignoreAppointmentId))
            ->contains(fn ($s) => $s['time'] === $start->format('H:i') && $s['available']);
    }

    private function at(Carbon $day, string $time): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $time.':0'));

        return $day->copy()->setTime($h, $m);
    }
}

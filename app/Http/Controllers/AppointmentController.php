<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Hairdresser;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\Agenda;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments, private Agenda $agenda) {}

    public function index(Request $request)
    {
        $date = $request->filled('data')
            ? Carbon::parse($request->input('data'), config('app.timezone'))
            : today();
        $hairdresserId = $request->integer('profissional') ?: null;

        $appointments = Appointment::with(['service', 'hairdresser', 'customer'])
            ->whereDate('scheduled_at', $date)
            ->when($hairdresserId, fn ($q) => $q->where('hairdresser_id', $hairdresserId))
            ->orderBy('scheduled_at')
            ->get();

        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthSameDay = now()->subMonthNoOverflow();

        $revenueMonth = (float) Transaction::where('type', 'income')->where('transaction_date', '>=', $monthStart)->sum('amount');
        $revenueLastMonth = (float) Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthSameDay])->sum('amount');
        $completedMonth = Appointment::where('status', 'completed')->where('scheduled_at', '>=', $monthStart)->count();

        $kpis = [
            'today' => Appointment::active()->whereDate('scheduled_at', today())->count(),
            'today_revenue' => (float) Appointment::active()->whereDate('scheduled_at', today())->sum('total_price'),
            'revenue_month' => $revenueMonth,
            'revenue_growth' => $revenueLastMonth > 0 ? (($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : null,
            'ticket' => $completedMonth ? (float) Appointment::where('status', 'completed')->where('scheduled_at', '>=', $monthStart)->avg('total_price') : 0,
            'new_customers' => Customer::where('created_at', '>=', $monthStart)->count(),
            'no_show_rate' => ($total = Appointment::whereIn('status', ['completed', 'no_show'])->where('scheduled_at', '>=', $monthStart)->count())
                ? Appointment::where('status', 'no_show')->where('scheduled_at', '>=', $monthStart)->count() / $total * 100 : 0,
            'pending_payment' => Appointment::where('status', 'pending_payment')->where('scheduled_at', '>=', now())->count(),
        ];

        // Clientes que não voltam há mais de 30 dias (oportunidade de reativação)
        $inactiveCustomers = Customer::query()
            ->whereNotNull('phone')
            ->whereHas('appointments', fn ($q) => $q->where('status', 'completed'))
            ->whereDoesntHave('appointments', fn ($q) => $q->where('scheduled_at', '>=', now()->subDays(30)))
            ->withMax('appointments', 'scheduled_at')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'appointments' => $appointments,
            'date' => $date,
            'hairdresserId' => $hairdresserId,
            'kpis' => $kpis,
            'inactiveCustomers' => $inactiveCustomers,
            'services' => Service::active()->ordered()->get(),
            'hairdressers' => Hairdresser::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $service = Service::findOrFail($data['service_id']);
        $start = Carbon::parse($data['scheduled_at'], config('app.timezone'));

        $conflict = Appointment::with('service')->active()
            ->where('hairdresser_id', $data['hairdresser_id'])
            ->whereDate('scheduled_at', $start)
            ->get()
            ->contains(fn ($a) => $start->lt($a->endsAt()) && $start->copy()->addMinutes($service->duration_minutes)->gt($a->scheduled_at));

        if ($conflict) {
            return back()->withErrors(['scheduled_at' => 'Este profissional já tem atendimento nesse horário.'])->withInput();
        }

        $customer = $this->appointments->findOrCreateCustomer($data['client_name'], $data['client_phone'] ?? null);

        $appointment = Appointment::create([
            ...$data,
            'client_phone' => $customer->phone,
            'scheduled_at' => $start,
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'total_price' => $service->price,
            'status' => 'scheduled',
            'payment_method' => 'pay_later',
            'payment_status' => 'pending',
        ]);

        $this->appointments->notify($appointment, 'confirmation');

        return redirect()->route('dashboard', ['data' => $start->format('Y-m-d')])
            ->with('status', 'Agendamento criado!');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $action = $request->validate(['action' => 'required|in:complete,cancel,no_show,paid,reopen'])['action'];
        $appointment->load(['service', 'customer']);

        match ($action) {
            'complete' => $this->appointments->complete($appointment),
            'cancel' => $this->appointments->cancel($appointment),
            'no_show' => $this->appointments->cancel($appointment, 'no_show'),
            'paid' => $this->appointments->markPaid($appointment),
            'reopen' => $appointment->update(['status' => 'scheduled', 'cancelled_at' => null]),
        };

        $messages = [
            'complete' => 'Atendimento concluído e registrado no financeiro.',
            'cancel' => 'Agendamento cancelado.',
            'no_show' => 'Marcado como "não compareceu".',
            'paid' => 'Pagamento registrado.',
            'reopen' => 'Agendamento reaberto.',
        ];

        return back()->with('status', $messages[$action]);
    }

    public function slots(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'service_id' => 'required|exists:services,id',
        ]);

        return response()->json($this->agenda->slots(
            Carbon::parse($data['date'], config('app.timezone')),
            Hairdresser::findOrFail($data['hairdresser_id']),
            Service::findOrFail($data['service_id']),
        ));
    }
}

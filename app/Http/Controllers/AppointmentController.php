<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $services = \App\Models\Service::all();
        $hairdressers = \App\Models\Hairdresser::all();
        // Agendamentos de hoje em diante
        $appointments = \App\Models\Appointment::with(['service', 'hairdresser'])
            ->whereDate('scheduled_at', '>=', \Carbon\Carbon::today())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('dashboard', compact('services', 'appointments', 'hairdressers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'scheduled_at' => 'required|date',
        ]);

        // Verificar disponibilidade (bloqueio de horário)
        $exists = \App\Models\Appointment::where('hairdresser_id', $data['hairdresser_id'])
            ->where('scheduled_at', $data['scheduled_at'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['scheduled_at' => 'Este cabeleireiro já tem um agendamento para este horário.'])->withInput();
        }

        $service = Service::find($data['service_id']);
        
        // Gestão Automatizada de Cliente
        $customer = Customer::where('phone', $data['client_phone'])->first();
        if (!$customer) {
            $customer = Customer::create([
                'name' => $data['client_name'],
                'phone' => $data['client_phone'],
            ]);
        }

        $data['total_price'] = $service->price;
        $data['status'] = 'scheduled';
        $data['user_id'] = auth()->id();
        $data['customer_id'] = $customer->id;
        $data['payment_method'] = 'presencial';
        $data['payment_status'] = 'pending';

        $appointment = Appointment::create($data);
        $customer->increment('loyalty_points', 1); // 1 ponto por serviço agendado manual
        
        // Enviar Notificação de Confirmação Imediata (Manual)
        try {
            $customer->notify(new \App\Notifications\AppointmentNotification($appointment, 'confirmation'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao enviar notificação manual: " . $e->getMessage());
        }
        
        // No dashboard adm, marcamos como pendente, então não incrementa total_spent ainda.
        // Se quiser incrementar no agendamento manual, descomente a linha abaixo:
        // $customer->increment('total_spent', $appointment->total_price);

        // Registrar no financeiro como receita agendada (ou pendente)
        // No walkthrough dizia que agendou -> receita registrada
        Transaction::create([
            'type' => 'income',
            'amount' => $appointment->total_price,
            'description' => "Corte Agendado: {$appointment->client_name} - {$service->name}",
            'appointment_id' => $appointment->id,
            'transaction_date' => now(),
        ]);

        return redirect()->back()->with('status', 'Agendamento realizado com sucesso!');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GuestBookingController extends Controller
{
    public function index()
    {
        $services = Service::all();
        $hairdressers = \App\Models\Hairdresser::all();
        return view('booking.index', compact('services', 'hairdressers'));
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'service_id' => 'required|exists:services,id',
            'hairdresser_id' => 'required|exists:hairdressers,id',
            'scheduled_at' => 'required|date|after:now',
            'payment_method' => 'required|in:pix,credit_card,pay_later',
        ]);

        // Verificar disponibilidade (bloqueio de horário)
        $exists = Appointment::where('hairdresser_id', $data['hairdresser_id'])
            ->where('scheduled_at', $data['scheduled_at'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Desculpe, este profissional já foi agendado para este exato horário.'
            ], 422);
        }

        $service = Service::find($data['service_id']);
        
        // Simulação de processamento de pagamento
        $data['total_price'] = $service->price;
        $data['payment_status'] = $data['payment_method'] === 'pay_later' ? 'pending' : 'paid';
        $data['status'] = 'scheduled';

        $appointment = Appointment::create($data);

        // Registrar no módulo financeiro apenas se pago agora
        if ($appointment->payment_status === 'paid') {
            try {
                Transaction::create([
                    'type' => 'income',
                    'amount' => $appointment->total_price,
                    'description' => "Agendamento Online (" . ($data['payment_method'] ?? 'N/A') . "): " . ($appointment->client_name ?? 'Cliente') . " - " . ($service->name ?? 'Serviço'),
                    'appointment_id' => $appointment->id,
                    'transaction_date' => now(),
                ]);
            } catch (\Exception $e) {
                // Log the error but don't fail the whole booking if only transaction fails
                \Illuminate\Support\Facades\Log::error("Erro ao registrar transação: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento realizado com sucesso!',
            'appointment_id' => $appointment->id
        ]);
    }

    public function success($id)
    {
        $appointment = Appointment::with('service')->findOrFail($id);
        return view('booking.success', compact('appointment'));
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->query('date');
        $hairdresserId = $request->query('hairdresser_id');

        if (!$date || !$hairdresserId) {
            return response()->json([]);
        }

        $busySlots = Appointment::where('hairdresser_id', $hairdresserId)
            ->whereDate('scheduled_at', $date)
            ->pluck('scheduled_at')
            ->map(function ($dt) {
                return \Carbon\Carbon::parse($dt)->format('H:i');
            });

        return response()->json($busySlots);
    }
}

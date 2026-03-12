<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;

class FinanceController extends Controller
{
    public function index()
    {
        $transactions = Transaction::orderBy('transaction_date', 'desc')->get();
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Cálculo de Comissões por Profissional
        $hairdressers = \App\Models\Hairdresser::with(['appointments' => function($q) {
            $q->where('status', '!=', 'cancelled');
        }])->get();
        
        $commissions = $hairdressers->map(function ($h) {
            $pending = $h->appointments->where('commission_status', 'pending')->sum(function ($a) use ($h) {
                return ($a->total_price * $h->commission_percent) / 100;
            });
            $paid = $h->appointments->where('commission_status', 'paid')->sum(function ($a) use ($h) {
                return ($a->total_price * $h->commission_percent) / 100;
            });
            
            return [
                'id' => $h->id,
                'name' => $h->name,
                'pending' => $pending,
                'paid' => $paid,
                'total' => $pending + $paid
            ];
        });

        return view('finance.index', compact('transactions', 'totalIncome', 'totalExpense', 'balance', 'commissions'));
    }

    public function payCommissions($hairdresserId)
    {
        \App\Models\Appointment::where('hairdresser_id', $hairdresserId)
            ->where('commission_status', 'pending')
            ->update(['commission_status' => 'paid']);

        return redirect()->back()->with('status', 'Comissões marcadas como pagas para este profissional!');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        Transaction::create($data);

        return redirect()->back()->with('status', 'Transação registrada!');
    }
}

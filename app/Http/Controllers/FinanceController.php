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

        // Comissões: só contam atendimentos concluídos
        $hairdressers = \App\Models\Hairdresser::with(['appointments' => function($q) {
            $q->where('status', 'completed');
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

        // Dados para o gráfico (Últimos 7 dias)
        $chartData = Transaction::selectRaw('DATE(transaction_date) as date, type, SUM(amount) as total')
            ->where('transaction_date', '>=', now()->subDays(7))
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('finance.index', compact('transactions', 'totalIncome', 'totalExpense', 'balance', 'commissions', 'chartData'));
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $data = array_map(function($line) {
            return str_getcsv($line, ';'); // Mudando para ; comum no BR
        }, file($path));

        array_shift($data); // cabeçalho

        $imported = 0;
        $skipped = 0;
        foreach ($data as $row) {
            $parsed = count($row) >= 4 ? $this->parseCsvRow($row) : null;
            if (! $parsed) {
                $skipped++;
                continue;
            }

            Transaction::create($parsed);
            $imported++;
        }

        $message = "{$imported} lançamento(s) importado(s).";
        if ($skipped) {
            $message .= " {$skipped} linha(s) ignorada(s) por data ou valor inválido.";
        }

        return redirect()->back()->with('status', $message);
    }

    /** Linha "Data;Descrição;Tipo;Valor" no formato brasileiro (14/11/2026; 1.234,56; Entrada/Saída). */
    private function parseCsvRow(array $row): ?array
    {
        [$rawDate, $description, $rawType, $rawAmount] = array_map('trim', array_slice($row, 0, 4));

        $date = null;
        foreach (['d/m/Y', 'd/m/y', 'Y-m-d'] as $format) {
            $candidate = \DateTimeImmutable::createFromFormat('!'.$format, $rawDate);
            if ($candidate && $candidate->format($format) === $rawDate) {
                $date = $candidate;
                break;
            }
        }

        $amount = preg_replace('/[^\d,.\-]/', '', $rawAmount);
        if (str_contains($amount, ',')) {
            $amount = str_replace(['.', ','], ['', '.'], $amount); // 1.234,56 -> 1234.56
        }

        if (! $date || $description === '' || ! is_numeric($amount)) {
            return null;
        }

        $type = \Illuminate\Support\Str::of($rawType)->ascii()->lower()->toString();

        return [
            'transaction_date' => $date->format('Y-m-d'),
            'description' => $description,
            'type' => in_array($type, ['entrada', 'receita', 'income'], true) ? 'income' : 'expense',
            'amount' => abs((float) $amount),
        ];
    }

    public function payCommissions($hairdresserId)
    {
        \App\Models\Appointment::where('hairdresser_id', $hairdresserId)
            ->where('status', 'completed')
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

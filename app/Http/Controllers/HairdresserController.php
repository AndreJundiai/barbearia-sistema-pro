<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use Illuminate\Http\Request;

class HairdresserController extends Controller
{
    public function index()
    {
        $hairdressers = Hairdresser::all();
        return view('hairdressers.index', compact('hairdressers'));
    }

    public function create()
    {
        return view('hairdressers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'instagram' => 'nullable|string|max:60',
        ]);
        $validated['active'] = $request->boolean('active');
        $validated['instagram'] = ltrim((string) ($validated['instagram'] ?? ''), '@') ?: null;

        Hairdresser::create($validated);

        return redirect()->route('hairdressers.index')->with('status', 'Cabeleireiro cadastrado com sucesso!');
    }

    public function edit(Hairdresser $hairdresser)
    {
        return view('hairdressers.edit', compact('hairdresser'));
    }

    public function update(Request $request, Hairdresser $hairdresser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'instagram' => 'nullable|string|max:60',
        ]);
        $validated['active'] = $request->boolean('active');
        $validated['instagram'] = ltrim((string) ($validated['instagram'] ?? ''), '@') ?: null;

        $hairdresser->update($validated);

        return redirect()->route('hairdressers.index')->with('status', 'Cabeleireiro atualizado com sucesso!');
    }

    public function destroy(Hairdresser $hairdresser)
    {
        if ($hairdresser->appointments()->exists()) {
            $hairdresser->update(['active' => false]);

            return redirect()->route('hairdressers.index')->with('status', 'Profissional possui histórico e foi desativado (não aparece mais no site).');
        }

        $hairdresser->delete();
        return redirect()->route('hairdressers.index')->with('status', 'Profissional removido com sucesso!');
    }
}

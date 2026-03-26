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
        ]);

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
        ]);

        $hairdresser->update($validated);

        return redirect()->route('hairdressers.index')->with('status', 'Cabeleireiro atualizado com sucesso!');
    }

    public function destroy(Hairdresser $hairdresser)
    {
        $hairdresser->delete();
        return redirect()->route('hairdressers.index')->with('status', 'Cabeleireiro removido com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YrdrrConsultation;
use Illuminate\Support\Facades\Auth;

class YrdrrController extends Controller
{
    public function index()
    {
        $consultations = Auth::user()->yrdrrConsultations()->latest()->get();
        return view('yrdrr.index', compact('consultations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'face_shape' => 'required|string',
            'style_notes' => 'required|string',
            'signature_fragrance' => 'nullable|string',
        ]);

        Auth::user()->yrdrrConsultations()->create($validated);

        return redirect()->back()->with('success', 'Perfil de Estilo YRDRR atualizado com sucesso.');
    }
}
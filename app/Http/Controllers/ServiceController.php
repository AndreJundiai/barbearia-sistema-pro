<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::ordered()->withCount(['appointments' => fn ($q) => $q->where('status', 'completed')])->get();

        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        Service::create($this->validated($request));

        return back()->with('status', 'Serviço criado!');
    }

    public function update(Request $request, Service $service)
    {
        $service->update($this->validated($request));

        return back()->with('status', 'Serviço atualizado!');
    }

    public function destroy(Service $service)
    {
        // Serviços com histórico são apenas desativados para não apagar o financeiro
        if ($service->appointments()->exists()) {
            $service->update(['active' => false]);

            return back()->with('status', 'Serviço possui histórico e foi desativado (não aparece mais no site).');
        }

        $service->delete();

        return back()->with('status', 'Serviço removido.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        return [
            ...$data,
            'sort_order' => $data['sort_order'] ?? 0,
            'active' => $request->boolean('active'),
            'featured' => $request->boolean('featured'),
        ];
    }
}

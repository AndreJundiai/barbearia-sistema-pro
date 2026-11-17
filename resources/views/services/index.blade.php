<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">Serviços e preços</h2>
            <span class="text-sm text-gray-500">Aparecem no site e no agendamento</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        @php $field = 'mt-1 w-full rounded-lg border-gray-300 text-sm'; @endphp

        <details class="bg-white rounded-2xl border border-gray-200 p-5" @if($errors->any()) open @endif>
            <summary class="font-semibold cursor-pointer">+ Novo serviço</summary>
            <form method="POST" action="{{ route('services.store') }}" class="grid sm:grid-cols-6 gap-3 mt-4">
                @csrf
                <div class="sm:col-span-3"><label class="text-xs text-gray-600">Nome</label><input name="name" required class="{{ $field }}" value="{{ old('name') }}"></div>
                <div class="sm:col-span-1"><label class="text-xs text-gray-600">Preço (R$)</label><input name="price" type="number" step="0.01" min="0" required class="{{ $field }}" value="{{ old('price') }}"></div>
                <div class="sm:col-span-1"><label class="text-xs text-gray-600">Duração (min)</label><input name="duration_minutes" type="number" min="5" step="5" required class="{{ $field }}" value="{{ old('duration_minutes', 30) }}"></div>
                <div class="sm:col-span-1"><label class="text-xs text-gray-600">Ordem</label><input name="sort_order" type="number" class="{{ $field }}" value="{{ old('sort_order', 0) }}"></div>
                <div class="sm:col-span-6"><label class="text-xs text-gray-600">Descrição (aparece no site)</label><input name="description" class="{{ $field }}" value="{{ old('description') }}"></div>
                <div class="sm:col-span-6 flex flex-wrap items-center gap-5 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" name="active" value="1" checked class="rounded"> Ativo</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="featured" value="1" class="rounded"> Destaque "Mais pedido"</label>
                    <button class="ml-auto bg-gray-900 text-white font-semibold px-5 py-2 rounded-lg">Criar serviço</button>
                </div>
            </form>
        </details>

        <div class="space-y-3">
            @forelse ($services as $service)
                <form method="POST" action="{{ route('services.update', $service) }}" class="bg-white rounded-2xl border {{ $service->active ? 'border-gray-200' : 'border-dashed border-gray-300 opacity-70' }} p-5 grid sm:grid-cols-6 gap-3">
                    @csrf @method('PUT')
                    <div class="sm:col-span-3"><label class="text-xs text-gray-600">Nome</label><input name="name" required class="{{ $field }}" value="{{ $service->name }}"></div>
                    <div class="sm:col-span-1"><label class="text-xs text-gray-600">Preço (R$)</label><input name="price" type="number" step="0.01" min="0" required class="{{ $field }}" value="{{ number_format($service->price, 2, '.', '') }}"></div>
                    <div class="sm:col-span-1"><label class="text-xs text-gray-600">Duração (min)</label><input name="duration_minutes" type="number" min="5" step="5" required class="{{ $field }}" value="{{ $service->duration_minutes }}"></div>
                    <div class="sm:col-span-1"><label class="text-xs text-gray-600">Ordem</label><input name="sort_order" type="number" class="{{ $field }}" value="{{ $service->sort_order }}"></div>
                    <div class="sm:col-span-6"><label class="text-xs text-gray-600">Descrição</label><input name="description" class="{{ $field }}" value="{{ $service->description }}"></div>
                    <div class="sm:col-span-6 flex flex-wrap items-center gap-5 text-sm">
                        <label class="flex items-center gap-2"><input type="checkbox" name="active" value="1" @checked($service->active) class="rounded"> Ativo</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="featured" value="1" @checked($service->featured) class="rounded"> Destaque</label>
                        <span class="text-xs text-gray-400">{{ $service->appointments_count }} atendimento(s) concluído(s)</span>
                        <div class="ml-auto flex items-center gap-3">
                            <button form="delete-{{ $service->id }}" class="text-red-600 text-sm">Excluir</button>
                            <button class="bg-gray-900 text-white font-semibold px-5 py-2 rounded-lg">Salvar</button>
                        </div>
                    </div>
                </form>
                <form id="delete-{{ $service->id }}" method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Excluir este serviço?')" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @empty
                <p class="text-gray-500 text-center py-10">Nenhum serviço cadastrado ainda.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>

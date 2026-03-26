<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Profissional') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-8 border border-gold/20">
                <form action="{{ route('hairdressers.update', $hairdresser) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome Completo</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $hairdresser->name) }}" required class="mt-1 block w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-gold focus:border-gold dark:text-gray-100">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $hairdresser->phone) }}" class="mt-1 block w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-gold focus:border-gold dark:text-gray-100">
                            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="specialty" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidade</label>
                            <input type="text" name="specialty" id="specialty" value="{{ old('specialty', $hairdresser->specialty) }}" class="mt-1 block w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-gold focus:border-gold dark:text-gray-100">
                            @error('specialty') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Biografia / Descrição</label>
                            <textarea name="bio" id="bio" rows="3" class="mt-1 block w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-gold focus:border-gold dark:text-gray-100">{{ old('bio', $hairdresser->bio) }}</textarea>
                            @error('bio') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="commission_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comissão (%)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" step="0.5" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', $hairdresser->commission_percent) }}" required class="block w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-gold focus:border-gold dark:text-gray-100">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            @error('commission_percent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-4 flex items-center justify-between">
                            <a href="{{ route('hairdressers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 border-b border-transparent hover:border-gray-400 transition-all">Sair sem salvar</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-10 rounded-lg shadow-lg transition-all transform hover:scale-105 uppercase tracking-widest text-xs">
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .focus\:ring-gold:focus { --tw-ring-color: #D4AF37; }
        .focus\:border-gold:focus { border-color: #D4AF37; }
    </style>
</x-app-layout>

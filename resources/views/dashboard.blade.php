<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Painel de Agendamentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Formulário de Agendamento -->
                <div class="md:col-span-1 bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium mb-4">Novo Agendamento</h3>
                    <form action="{{ route('appointments.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Cliente</label>
                            <input type="text" name="client_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input type="text" name="client_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Serviço</label>
                            <select name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} - R$ {{ number_format($service->price, 2, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Cabeleireiro</label>
                            <select name="hairdresser_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Selecione um profissional</option>
                                @foreach($hairdressers as $hairdresser)
                                    <option value="{{ $hairdresser->id }}">{{ $hairdresser->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Data/Hora</label>
                            <input type="datetime-local" name="scheduled_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <button type="submit" class="w-full bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">Agendar</button>
                    </form>
                </div>

                <!-- Lista de Agendamentos -->
                <div class="md:col-span-2 bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium mb-4">Próximos Cortes</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Hora</th>
                                    <th class="py-2">Cliente</th>
                                    <th class="py-2">Serviço</th>
                                    <th class="py-2">Profissional</th>
                                    <th class="py-2">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appointment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2">{{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('H:i') }}</td>
                                    <td class="py-2 font-medium">{{ $appointment->client_name }}</td>
                                    <td class="py-2">{{ $appointment->service->name }}</td>
                                    <td class="py-2 text-xs text-indigo-600">{{ $appointment->hairdresser->name ?? 'N/A' }}</td>
                                    <td class="py-2 text-green-600 font-bold">R$ {{ number_format($appointment->total_price, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                @if($appointments->isEmpty())
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">Nenhum agendamento para hoje.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

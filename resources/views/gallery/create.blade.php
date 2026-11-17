<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova foto na galeria</h2>
    </x-slot>

    <div class="py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('gallery.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Foto</label>
                    <input type="file" name="image" accept="image/*" required class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gold-100 file:text-gold-800">
                    @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">Até 4 MB. Fotos verticais ficam melhores.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Título (opcional)</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border-gray-300" placeholder="Ex.: Degradê navalhado">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Descrição (opcional)</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300">{{ old('description') }}</textarea>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <a href="{{ route('gallery.index') }}" class="text-sm text-gray-500">Cancelar</a>
                    <button class="bg-gray-900 text-white font-semibold px-6 py-2.5 rounded-lg">Publicar foto</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

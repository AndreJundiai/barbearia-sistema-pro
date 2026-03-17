@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black pt-20 pb-12 px-4">
    <div class="max-w-7xl mx-auto">
        <header class="text-center mb-16">
            <h1 class="font-luxury text-5xl text-gold mb-4">Galeria de Estilos</h1>
            <p class="text-gray-400 uppercase tracking-widest text-sm">Inspire-se com os melhores trabalhos da Elite</p>
            <div class="w-24 h-1 bg-gold mx-auto mt-6 rounded-full"></div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($images as $image)
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/5] bg-gray-900 border border-gray-800 transition-all hover:border-gold/50">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-80"></div>
                    <div class="absolute bottom-0 left-0 p-6 w-full transform translate-y-2 transition-transform group-hover:translate-y-0">
                        <h3 class="text-white font-bold text-xl mb-1">{{ $image->title }}</h3>
                        <p class="text-gray-400 text-sm italic">{{ $image->description }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="bg-gray-900/50 inline-block p-10 rounded-full border border-dashed border-gray-700">
                        <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 font-medium">Nossa galeria está sendo preparada para você.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .font-luxury { font-family: 'Playfair Display', serif; }
    .text-gold { color: #D4AF37; }
    .bg-gold { background-color: #D4AF37; }
</style>
@endsection

@extends('layouts.site')

@section('title', 'Galeria | '.$shop['shop_name'])
@section('description', 'Veja cortes e barbas feitos na '.$shop['shop_name'].' e agende o seu.')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-20">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
        <div>
            <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Galeria</div>
            <h1 class="font-display text-4xl sm:text-5xl font-bold text-white">Nossos trabalhos</h1>
        </div>
        <div class="flex gap-3">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('gallery.create') }}" class="border border-white/20 text-white px-5 py-3 rounded-full text-sm hover:border-gold">+ Adicionar foto</a>
                @endif
            @endauth
            <a href="{{ route('booking.index') }}" class="bg-gold text-ink font-semibold px-6 py-3 rounded-full text-sm">Quero esse estilo</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
    @endif

    @if ($images->isEmpty())
        <div class="rounded-2xl border border-dashed border-white/15 py-20 text-center text-neutral-500">
            Nossa galeria está sendo preparada. Enquanto isso, <a href="{{ route('booking.index') }}" class="text-gold underline">agende seu horário</a>.
        </div>
    @else
        <div class="columns-2 md:columns-3 gap-3 [&>*]:mb-3">
            @foreach ($images as $image)
                <figure class="relative group overflow-hidden rounded-xl bg-ink-800 break-inside-avoid">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->title ?: 'Trabalho da '.$shop['shop_name'] }}" loading="lazy" class="w-full h-auto">
                    @if ($image->title || $image->description)
                        <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-4 pt-10">
                            <div class="text-white font-semibold text-sm">{{ $image->title }}</div>
                            <div class="text-neutral-300 text-xs">{{ $image->description }}</div>
                        </figcaption>
                    @endif
                    @auth
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('gallery.destroy', $image) }}" onsubmit="return confirm('Remover esta foto?')" class="absolute top-2 right-2">
                                @csrf @method('DELETE')
                                <button class="bg-black/70 text-red-300 text-xs px-2.5 py-1.5 rounded-lg">Remover</button>
                            </form>
                        @endif
                    @endauth
                </figure>
            @endforeach
        </div>
    @endif
</div>
@endsection

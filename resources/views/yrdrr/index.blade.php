<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YRDRR - Experiência Elite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a0a0a; color: #d4af37; }
        .gold-border { border: 1px solid #d4af37; }
        .gold-gradient { background: linear-gradient(135deg, #d4af37 0%, #f9e29c 100%); }
        .luxury-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-12 border-b border-yellow-900/30 pb-6">
            <h1 class="text-4xl font-serif text-white">YRDRR <span class="text-sm font-light tracking-widest uppercase block text-gray-500">Relationship Record</span></h1>
            <div class="text-right">
                <p class="text-xs uppercase tracking-widest text-gray-400">Membro Exclusive</p>
                <p class="text-xl">Mr. {{ Auth::user()->name ?? 'Guest' }}</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="luxury-card p-6 rounded-lg gold-border">
                <h3 class="text-xl mb-4 font-serif">Perfil Visagista</h3>
                <div class="space-y-4 text-gray-300">
                    <div class="flex justify-between"><span>Formato de Rosto:</span> <span class="text-white italic">Oval Colonial</span></div>
                    <div class="flex justify-between"><span>Fragrância Signature:</span> <span class="text-white italic">Oud & Bergamot</span></div>
                    <div class="flex justify-between"><span>Preferência:</span> <span class="text-white italic">Toalha Quente (42°C)</span></div>
                </div>
            </div>

            <div class="col-span-2">
                <h3 class="text-2xl mb-6 font-serif text-white">Timeline de Estilo</h3>
                @forelse($consultations as $item)
                    <div class="luxury-card p-6 rounded-lg mb-4 border-l-4 border-yellow-600">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs text-gray-500 uppercase mb-1">{{ $item->created_at->format('d M, Y') }}</p>
                                <p class="text-white">{{ $item->style_notes }}</p>
                            </div>
                            <span class="gold-gradient text-black px-3 py-1 rounded-full text-xs font-bold">{{ $item->face_shape }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-gray-500 italic">Nenhum registro de estilo encontrado. Inicie sua jornada de luxo.</div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
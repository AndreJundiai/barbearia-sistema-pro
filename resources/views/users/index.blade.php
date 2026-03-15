<x-app-layout>
    <div class="py-12 bg-[#0a0a0b] min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-8">
                <div>
                    <h2 class="text-4xl font-extrabold tracking-tight text-white font-luxury">
                        Gestão de <span class="text-gold">Acesso</span>
                    </h2>
                    <p class="text-gray-500 mt-2 text-sm uppercase tracking-widest font-bold">Administradores e Colaboradores</p>
                </div>
            </div>

            @if(session('status'))
                <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- List Users -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-gray-900/50 border border-gray-800 rounded-3xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20 text-[10px] uppercase font-black text-gray-500 tracking-widest">
                                    <th class="px-6 py-4">Nome</th>
                                    <th class="px-6 py-4">E-mail</th>
                                    <th class="px-6 py-4">Perfil</th>
                                    <th class="px-6 py-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-800/30 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-200">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $user->role == 'admin' ? 'bg-gold/10 text-gold border border-gold/20' : 'bg-gray-500/10 text-gray-500 border border-gray-500/20' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este acesso?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-bold uppercase tracking-tighter">Remover</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Create Form -->
                <div class="bg-gray-900 shadow-2xl rounded-3xl p-6 border border-gray-800 h-fit">
                    <h3 class="text-md font-bold text-white mb-6 flex items-center gap-2 uppercase tracking-tighter">
                        Novo Administrador
                    </h3>
                    <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Nome</label>
                            <input type="text" name="name" required class="w-full bg-black border-gray-800 rounded-xl text-white text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">E-mail</label>
                            <input type="email" name="email" required class="w-full bg-black border-gray-800 rounded-xl text-white text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Senha Provisória</label>
                            <input type="password" name="password" required class="w-full bg-black border-gray-800 rounded-xl text-white text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Perfil de Acesso</label>
                            <select name="role" class="w-full bg-black border-gray-800 rounded-xl text-white text-sm">
                                <option value="admin">Administrador (Acesso Total)</option>
                                <option value="worker">Colaborador (Básico)</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-gold text-black font-black py-4 rounded-xl uppercase tracking-widest text-xs shadow-lg shadow-gold/10">
                            Cadastrar Acesso
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

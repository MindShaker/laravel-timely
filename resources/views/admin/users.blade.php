<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-content">Utilizadores</h2>
            <a href="{{ route('admin.users.create') }}">
                <x-primary-app-button>Novo utilizador</x-primary-app-button>
            </a>
        </div>
    </x-slot>

    @include('components.alerts')

    <div class="bg-surface border border-neutral-700 rounded-xl overflow-hidden">
        <table class="w-full text-sm text-content">
            <thead>
                <tr class="border-b border-neutral-700 text-content-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Nome</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">Horário</th>
                    <th class="px-4 py-3 text-left">Aniversário</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @foreach($users as $user)
                    <tr class="hover:bg-surface-hover transition">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-content-muted">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $user->tipo === 'admin' ? 'bg-amber-900/40 text-amber-300 ring-1 ring-amber-700/50' : 'bg-neutral-800 text-neutral-300 ring-1 ring-neutral-700' }}">
                                {{ ucfirst($user->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-content-muted font-mono text-xs">
                            {{ $user->hora_entrada }} – {{ $user->hora_saida }}
                            <span class="text-neutral-600 ml-1">(almoço {{ $user->inicio_almoco }})</span>
                        </td>
                        <td class="px-4 py-3 text-content-muted">
                            {{ $user->birthdate ? $user->birthdate->format('d/m') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.calendar', $user) }}"
                                   class="text-xs text-content-muted hover:text-primary transition px-2 py-1 rounded hover:bg-surface-hover">
                                    Calendário
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-xs text-content-muted hover:text-content transition px-2 py-1 rounded hover:bg-surface-hover">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Eliminar {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-950/30">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($users->isEmpty())
            <p class="text-center text-content-muted py-12">Nenhum utilizador encontrado.</p>
        @endif
    </div>
</x-app-layout>

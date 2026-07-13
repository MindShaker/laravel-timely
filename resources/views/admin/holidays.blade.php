<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">

            {{-- Year navigation --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.holidays', $year - 1) }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-content text-center w-20">{{ $year }}</h2>
                <a href="{{ route('admin.holidays', $year + 1) }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
                <span class="text-sm text-content-muted">{{ $holidays->count() }} feriados</span>
            </div>

            {{-- Sync button --}}
            <form method="POST" action="{{ route('admin.holidays.sync', $year) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-neutral-700 text-sm text-content-muted hover:text-content hover:border-neutral-500 transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Sincronizar API
                </button>
            </form>
        </div>
    </x-slot>

    @include('components.alerts')

    <div class="flex flex-col gap-6">

        {{-- Holidays table --}}
        <div class="bg-surface border border-neutral-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-content">
                <thead>
                    <tr class="border-b border-neutral-700 text-content-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-36">Data</th>
                        <th class="px-4 py-3 text-left">Nome</th>
                        <th class="px-4 py-3 text-right w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse ($holidays as $holiday)
                        <tr x-data="{ editing: false, name: @js($holiday->name) }" class="hover:bg-surface-hover transition">
                            <td class="px-4 py-3 font-mono text-content-muted text-xs tabular-nums">
                                {{ $holiday->date->format('d M') }}
                            </td>
                            <td class="px-4 py-3">
                                <span x-show="!editing" x-text="name" class="text-content"></span>
                                <form x-show="editing" method="POST"
                                    action="{{ route('admin.holidays.update', $holiday) }}"
                                    @submit.prevent="$el.submit()" class="flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <input type="text" name="name" x-model="name"
                                        class="flex-1 bg-neutral-800 border border-neutral-600 rounded-lg px-2.5 py-1 text-sm text-content focus:outline-none focus:border-primary"
                                        @keydown.escape="editing = false; name = @js($holiday->name)">
                                    <button type="submit"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-primary/20 text-primary hover:bg-primary/30 transition">
                                        Guardar
                                    </button>
                                    <button type="button" @click="editing = false; name = @js($holiday->name)"
                                        class="text-xs px-2 py-1 rounded-lg text-content-muted hover:text-content hover:bg-surface-hover transition">
                                        Cancelar
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button x-show="!editing" @click="editing = true"
                                        class="text-xs text-content-muted hover:text-content transition px-2 py-1 rounded hover:bg-surface-hover">
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}"
                                        onsubmit="return confirm('Remover {{ $holiday->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-950/30">
                                            Remover
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center text-content-muted">
                                Sem feriados para {{ $year }}. Use "Sincronizar API" para importar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add holiday form --}}
        <div class="bg-surface border border-neutral-700 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-content mb-4">Adicionar feriado</h3>
            <form method="POST" action="{{ route('admin.holidays.store') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs text-content-muted uppercase tracking-wide">Data</label>
                    <input type="date" name="date"
                        value="{{ old('date', $year . '-01-01') }}"
                        min="{{ $year }}-01-01" max="{{ $year }}-12-31"
                        class="bg-neutral-800 border border-neutral-600 rounded-lg px-3 py-1.5 text-sm text-content focus:outline-none focus:border-primary
                            @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col gap-1.5 flex-1 min-w-52">
                    <label class="text-xs text-content-muted uppercase tracking-wide">Nome</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="ex: São João Baptista"
                        class="bg-neutral-800 border border-neutral-600 rounded-lg px-3 py-1.5 text-sm text-content focus:outline-none focus:border-primary
                            @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="px-4 py-1.5 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary/90 transition">
                    Adicionar
                </button>
            </form>
        </div>

    </div>
</x-app-layout>

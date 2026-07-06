<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-content">Exportar Registo de Ponto</h2>
    </x-slot>

    @include('components.alerts')

    <div class="max-w-md">
        <form method="GET" action="{{ route('admin.export.download') }}"
              class="bg-surface border border-neutral-700 rounded-xl p-6 space-y-5">

            <div>
                <x-input-label for="user_id" value="Colaborador" />
                <select id="user_id" name="user_id"
                    class="mt-1 w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring">
                    <option value="">Selecionar colaborador…</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-input-label for="month" value="Mês" />
                <x-text-input id="month" name="month" type="month" class="mt-1 w-full"
                    value="{{ old('month', now()->format('Y-m')) }}" required />
                @error('month')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <x-primary-app-button class="w-full justify-center">
                Descarregar Excel
            </x-primary-app-button>
        </form>

        <p class="text-xs text-content-muted mt-4 text-center">
            O ficheiro inclui todos os dias úteis com o horário do colaborador.<br>
            Dias de férias, feriados e fins-de-semana são assinalados automaticamente.
        </p>
    </div>
</x-app-layout>

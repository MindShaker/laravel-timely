<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users') }}" class="text-content-muted hover:text-content text-sm transition">← Utilizadores</a>
            <span class="text-neutral-600">/</span>
            <h2 class="text-lg font-semibold text-content">
                {{ isset($user) ? 'Editar ' . $user->name : 'Novo Utilizador' }}
            </h2>
        </div>
    </x-slot>

    @include('components.alerts')

    <div class="w-full">
        <form method="POST"
            action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}"
            class="bg-surface border border-neutral-700 rounded-xl p-6 space-y-5">
            @csrf
            @if (isset($user))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 w-full"
                        value="{{ old('name', $user->name ?? '') }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 w-full"
                        value="{{ old('email', $user->email ?? '') }}" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="password" value="{{ isset($user) ? 'Nova password (opcional)' : 'Password' }}" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 w-full" @required="isset($user)" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo"
                        class="mt-1 w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring">
                        <option value="user" {{ old('tipo', $user->tipo ?? '') === 'user' ? 'selected' : '' }}>Utilizador</option>
                        <option value="admin" {{ old('tipo', $user->tipo ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('tipo')" class="mt-1" />
                </div>
            </div>

            <hr class="border-neutral-700">
            <p class="text-xs text-content-muted font-medium uppercase tracking-wide">Horário de trabalho</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <x-input-label for="hora_entrada" value="Hora de entrada" />
                    <x-text-input id="hora_entrada" name="hora_entrada" type="time" class="mt-1 w-full"
                        value="{{ old('hora_entrada', $user->hora_entrada ?? '09:00') }}" required />
                    <x-input-error :messages="$errors->get('hora_entrada')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="inicio_almoco" value="Início do almoço" />
                    <x-text-input id="inicio_almoco" name="inicio_almoco" type="time" class="mt-1 w-full"
                        value="{{ old('inicio_almoco', $user->inicio_almoco ?? '13:00') }}" required />
                    <x-input-error :messages="$errors->get('inicio_almoco')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="hora_saida" value="Hora de saída" />
                    <x-text-input id="hora_saida" name="hora_saida" type="time" class="mt-1 w-full"
                        value="{{ old('hora_saida', $user->hora_saida ?? '18:00') }}" required />
                    <x-input-error :messages="$errors->get('hora_saida')" class="mt-1" />
                </div>
            </div>

            <div class="max-w-xs">
                <x-input-label for="birthdate" value="Data de aniversário (opcional)" />
                <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 w-full"
                    value="{{ old('birthdate', isset($user) && $user->birthdate ? $user->birthdate->format('Y-m-d') : '') }}" />
                <x-input-error :messages="$errors->get('birthdate')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-app-button>
                    {{ isset($user) ? 'Guardar alterações' : 'Criar utilizador' }}
                </x-primary-app-button>
                <a href="{{ route('admin.users') }}">
                    <x-secondary-app-button type="button">Cancelar</x-secondary-app-button>
                </a>
            </div>
        </form>
    </div>
</x-app-layout>

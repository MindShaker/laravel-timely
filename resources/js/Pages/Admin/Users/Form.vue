<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: { type: Object, default: null },
});

const isEditing = computed(() => !!props.user);

const form = useForm({
    name:          props.user?.name          ?? '',
    email:         props.user?.email         ?? '',
    password:      '',
    tipo:          props.user?.tipo          ?? 'user',
    hora_entrada:  props.user?.hora_entrada  ?? '09:00',
    inicio_almoco: props.user?.inicio_almoco ?? '13:00',
    hora_saida:    props.user?.hora_saida    ?? '18:00',
    birthdate:     props.user?.birthdate_raw ?? '',
});

function submit() {
    if (isEditing.value) {
        form.put(route('admin.users.update', props.user.id));
    } else {
        form.post(route('admin.users.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? `Editar ${user.name}` : 'Novo Utilizador'" />

        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users')" class="text-content-muted hover:text-content text-sm transition">← Utilizadores</Link>
                <span class="text-neutral-600">/</span>
                <h2 class="text-lg font-semibold text-content">
                    {{ isEditing ? `Editar ${user.name}` : 'Novo Utilizador' }}
                </h2>
            </div>
        </template>

        <div class="w-full">
            <form @submit.prevent="submit" class="bg-surface border border-neutral-700 rounded-xl p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <InputLabel for="name" value="Nome" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 w-full" required />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="password" :value="isEditing ? 'Nova password (opcional)' : 'Password'" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 w-full" :required="!isEditing" />
                        <InputError :message="form.errors.password" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="tipo" value="Tipo" />
                        <select id="tipo" v-model="form.tipo"
                            class="mt-1 w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring">
                            <option value="user">Utilizador</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError :message="form.errors.tipo" class="mt-1" />
                    </div>
                </div>

                <hr class="border-neutral-700">
                <p class="text-xs text-content-muted font-medium uppercase tracking-wide">Horário de trabalho</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <InputLabel for="hora_entrada" value="Hora de entrada" />
                        <TextInput id="hora_entrada" v-model="form.hora_entrada" type="time" class="mt-1 w-full" required />
                        <InputError :message="form.errors.hora_entrada" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="inicio_almoco" value="Início do almoço" />
                        <TextInput id="inicio_almoco" v-model="form.inicio_almoco" type="time" class="mt-1 w-full" required />
                        <InputError :message="form.errors.inicio_almoco" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="hora_saida" value="Hora de saída" />
                        <TextInput id="hora_saida" v-model="form.hora_saida" type="time" class="mt-1 w-full" required />
                        <InputError :message="form.errors.hora_saida" class="mt-1" />
                    </div>
                </div>

                <div class="max-w-xs">
                    <InputLabel for="birthdate" value="Data de aniversário (opcional)" />
                    <TextInput id="birthdate" v-model="form.birthdate" type="date" class="mt-1 w-full" />
                    <InputError :message="form.errors.birthdate" class="mt-1" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">
                        {{ isEditing ? 'Guardar alterações' : 'Criar utilizador' }}
                    </PrimaryButton>
                    <Link :href="route('admin.users')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

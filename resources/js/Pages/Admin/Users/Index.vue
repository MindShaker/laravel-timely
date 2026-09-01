<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
});

function deleteUser(user) {
    if (confirm(`Eliminar ${user.name}?`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Utilizadores" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-content">Utilizadores</h2>
                <Link :href="route('admin.users.create')">
                    <PrimaryButton>Novo utilizador</PrimaryButton>
                </Link>
            </div>
        </template>

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
                    <tr v-for="user in users" :key="user.id" class="hover:bg-surface-hover transition">
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-content-muted">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                :class="user.tipo === 'admin'
                                    ? 'bg-amber-900/40 text-amber-300 ring-1 ring-amber-700/50'
                                    : 'bg-neutral-800 text-neutral-300 ring-1 ring-neutral-700'">
                                {{ user.tipo.charAt(0).toUpperCase() + user.tipo.slice(1) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-content-muted font-mono text-xs">
                            {{ user.hora_entrada }} – {{ user.hora_saida }}
                            <span class="text-neutral-600 ml-1">(almoço {{ user.inicio_almoco }})</span>
                        </td>
                        <td class="px-4 py-3 text-content-muted">{{ user.birthdate_formatted ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('admin.calendar', user.id)"
                                    class="text-xs text-content-muted hover:text-primary transition px-2 py-1 rounded hover:bg-surface-hover">
                                    Calendário
                                </Link>
                                <Link :href="route('admin.users.edit', user.id)"
                                    class="text-xs text-content-muted hover:text-content transition px-2 py-1 rounded hover:bg-surface-hover">
                                    Editar
                                </Link>
                                <button @click="deleteUser(user)"
                                    class="text-xs text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-950/30">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!users.length" class="text-center text-content-muted py-12">Nenhum utilizador encontrado.</p>
        </div>
    </AppLayout>
</template>

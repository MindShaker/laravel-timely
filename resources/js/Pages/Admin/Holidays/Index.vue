<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    year: Number,
    holidays: Array,
});

const editingId = ref(null);
const editName = ref('');

const addForm = useForm({
    date: computed(() => `${props.year}-01-01`).value,
    name: '',
});

function startEdit(holiday) {
    editingId.value = holiday.id;
    editName.value = holiday.name;
}

function cancelEdit() {
    editingId.value = null;
    editName.value = '';
}

function saveEdit(holiday) {
    router.patch(route('admin.holidays.update', holiday.id), { name: editName.value }, {
        onSuccess: () => cancelEdit(),
    });
}

function deleteHoliday(holiday) {
    if (confirm(`Remover ${holiday.name}?`)) {
        router.delete(route('admin.holidays.destroy', holiday.id));
    }
}

function syncHolidays() {
    router.post(route('admin.holidays.sync', props.year));
}

function addHoliday() {
    addForm.post(route('admin.holidays.store'), {
        onSuccess: () => addForm.reset('name'),
    });
}
</script>

<template>
    <AppLayout>
        <Head :title="`Feriados ${year}`" />

        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <a :href="route('admin.holidays', year - 1)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </a>
                    <h2 class="text-lg font-semibold text-content text-center w-20">{{ year }}</h2>
                    <a :href="route('admin.holidays', year + 1)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    <span class="text-sm text-content-muted">{{ holidays.length }} feriados</span>
                </div>

                <button @click="syncHolidays"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-neutral-700 text-sm text-content-muted hover:text-content hover:border-neutral-500 transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Sincronizar API
                </button>
            </div>
        </template>

        <div class="flex flex-col gap-6">

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
                        <template v-if="holidays.length">
                            <tr v-for="holiday in holidays" :key="holiday.id" class="hover:bg-surface-hover transition">
                                <td class="px-4 py-3 font-mono text-content-muted text-xs tabular-nums">
                                    {{ holiday.date_formatted }}
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="editingId !== holiday.id" class="text-content">{{ holiday.name }}</span>
                                    <div v-else class="flex items-center gap-2">
                                        <input v-model="editName" type="text"
                                            class="flex-1 bg-neutral-800 border border-neutral-600 rounded-lg px-2.5 py-1 text-sm text-content focus:outline-none focus:border-primary"
                                            @keydown.escape="cancelEdit"
                                            @keydown.enter.prevent="saveEdit(holiday)"
                                        >
                                        <button @click="saveEdit(holiday)"
                                            class="text-xs px-2.5 py-1 rounded-lg bg-primary/20 text-primary hover:bg-primary/30 transition">
                                            Guardar
                                        </button>
                                        <button @click="cancelEdit"
                                            class="text-xs px-2 py-1 rounded-lg text-content-muted hover:text-content hover:bg-surface-hover transition">
                                            Cancelar
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button v-if="editingId !== holiday.id" @click="startEdit(holiday)"
                                            class="text-xs text-content-muted hover:text-content transition px-2 py-1 rounded hover:bg-surface-hover">
                                            Editar
                                        </button>
                                        <button @click="deleteHoliday(holiday)"
                                            class="text-xs text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-950/30">
                                            Remover
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="3" class="px-4 py-12 text-center text-content-muted">
                                Sem feriados para {{ year }}. Use "Sincronizar API" para importar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-surface border border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-content mb-4">Adicionar feriado</h3>
                <form @submit.prevent="addHoliday" class="flex flex-wrap gap-3 items-end">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs text-content-muted uppercase tracking-wide">Data</label>
                        <input v-model="addForm.date" type="date"
                            :min="`${year}-01-01`" :max="`${year}-12-31`"
                            class="bg-neutral-800 border rounded-lg px-3 py-1.5 text-sm text-content focus:outline-none focus:border-primary"
                            :class="addForm.errors.date ? 'border-red-500' : 'border-neutral-600'">
                        <p v-if="addForm.errors.date" class="text-xs text-red-400">{{ addForm.errors.date }}</p>
                    </div>
                    <div class="flex flex-col gap-1.5 flex-1 min-w-52">
                        <label class="text-xs text-content-muted uppercase tracking-wide">Nome</label>
                        <input v-model="addForm.name" type="text" placeholder="ex: São João Baptista"
                            class="bg-neutral-800 border rounded-lg px-3 py-1.5 text-sm text-content focus:outline-none focus:border-primary"
                            :class="addForm.errors.name ? 'border-red-500' : 'border-neutral-600'">
                        <p v-if="addForm.errors.name" class="text-xs text-red-400">{{ addForm.errors.name }}</p>
                    </div>
                    <button type="submit" :disabled="addForm.processing"
                        class="px-4 py-1.5 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary/90 transition disabled:opacity-50">
                        Adicionar
                    </button>
                </form>
            </div>

        </div>
    </AppLayout>
</template>

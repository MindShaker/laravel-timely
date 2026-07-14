<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const userId = ref('');
const month = ref(new Date().toISOString().slice(0, 7));
const error = ref('');

function download() {
    if (!userId.value) {
        error.value = 'Selecione um colaborador.';
        return;
    }
    error.value = '';
    const url = new URL(route('admin.export.download'), window.location.origin);
    url.searchParams.set('user_id', userId.value);
    url.searchParams.set('month', month.value);
    window.location.href = url.toString();
}
</script>

<template>
    <AppLayout>
        <Head title="Exportar" />

        <template #header>
            <h2 class="text-lg font-semibold text-content">Exportar Registo de Ponto</h2>
        </template>

        <div class="max-w-md">
            <div class="bg-surface border border-neutral-700 rounded-xl p-6 space-y-5">
                <div>
                    <InputLabel for="user_id" value="Colaborador" />
                    <select id="user_id" v-model="userId"
                        class="mt-1 w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring">
                        <option value="">Selecionar colaborador…</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <p v-if="error" class="text-red-400 text-xs mt-1">{{ error }}</p>
                </div>

                <div>
                    <InputLabel for="month" value="Mês" />
                    <TextInput id="month" v-model="month" type="month" class="mt-1 w-full" required />
                </div>

                <PrimaryButton class="w-full justify-center" @click="download">
                    Descarregar Excel
                </PrimaryButton>
            </div>

            <p class="text-xs text-content-muted mt-4 text-center">
                O ficheiro inclui todos os dias úteis com o horário do colaborador.<br>
                Dias de férias, feriados e fins-de-semana são assinalados automaticamente.
            </p>
        </div>
    </AppLayout>
</template>

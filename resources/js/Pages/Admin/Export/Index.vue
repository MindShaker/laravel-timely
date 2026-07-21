<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { computed, inject, ref } from 'vue';

const props = defineProps({ users: Array });
const route = inject('route');

const selectedIds = ref([]);
const fromMonth   = ref(new Date().toISOString().slice(0, 7));
const toMonth     = ref(new Date().toISOString().slice(0, 7));
const error       = ref('');
const isLoading   = ref(false);

const allSelected = computed(() =>
    props.users.length > 0 && selectedIds.value.length === props.users.length
);

const monthCount = computed(() => {
    if (!fromMonth.value || !toMonth.value || toMonth.value < fromMonth.value) return 0;
    const [fy, fm] = fromMonth.value.split('-').map(Number);
    const [ty, tm] = toMonth.value.split('-').map(Number);
    return (ty - fy) * 12 + (tm - fm) + 1;
});

function toggleAll() {
    selectedIds.value = allSelected.value ? [] : props.users.map(u => u.id);
}

function toggleUser(id) {
    const idx = selectedIds.value.indexOf(id);
    if (idx >= 0) selectedIds.value.splice(idx, 1);
    else selectedIds.value.push(id);
}

function isSelected(id) {
    return selectedIds.value.includes(id);
}

function download() {
    if (selectedIds.value.length === 0) {
        error.value = 'Seleciona pelo menos um colaborador.';
        return;
    }
    if (toMonth.value < fromMonth.value) {
        error.value = 'O mês de fim deve ser igual ou posterior ao de início.';
        return;
    }
    error.value = '';

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('admin.export.download');
    form.style.display = 'none';

    const csrf = document.createElement('input');
    csrf.name  = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);

    selectedIds.value.forEach(id => {
        const el = document.createElement('input');
        el.name  = 'user_ids[]';
        el.value = id;
        form.appendChild(el);
    });

    [['from', fromMonth.value], ['to', toMonth.value]].forEach(([name, val]) => {
        const el = document.createElement('input');
        el.name  = name;
        el.value = val;
        form.appendChild(el);
    });

    document.body.appendChild(form);
    isLoading.value = true;
    form.submit();
    document.body.removeChild(form);
    setTimeout(() => { isLoading.value = false; }, 10000);
}
</script>

<template>
    <AppLayout>
        <Head title="Exportar" />

        <template #header>
            <h2 class="text-lg font-semibold text-content">Exportar Registo de Ponto</h2>
        </template>

        <div class="max-w-2xl space-y-4">
            <div class="bg-surface border border-neutral-700 rounded-xl p-6 space-y-6">

                <!-- Collaborators -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <InputLabel value="Colaboradores" />
                        <button type="button" @click="toggleAll"
                            class="text-xs text-content-muted hover:text-content transition-colors">
                            {{ allSelected ? 'Desselecionar todos' : 'Selecionar todos' }}
                        </button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button
                            v-for="u in users"
                            :key="u.id"
                            type="button"
                            @click="toggleUser(u.id)"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border text-left transition-colors"
                            :class="isSelected(u.id)
                                ? 'border-cyan-600 bg-cyan-500/10 text-content'
                                : 'border-neutral-700 hover:border-neutral-500 text-content-muted hover:text-content'"
                        >
                            <span class="w-4 h-4 rounded border shrink-0 flex items-center justify-center"
                                :class="isSelected(u.id)
                                    ? 'bg-cyan-500 border-cyan-500'
                                    : 'border-neutral-600'">
                                <svg v-if="isSelected(u.id)" class="w-2.5 h-2.5 text-neutral-900" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="text-sm truncate">{{ u.name }}</span>
                        </button>
                    </div>
                </div>

                <!-- Date range -->
                <div>
                    <InputLabel value="Período" class="mb-3" />
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-content-muted mb-1">De</p>
                            <input
                                v-model="fromMonth"
                                type="month"
                                class="w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring"
                            />
                        </div>
                        <div>
                            <p class="text-xs text-content-muted mb-1">Até</p>
                            <input
                                v-model="toMonth"
                                type="month"
                                class="w-full bg-input border border-input-border text-content rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-input-ring"
                            />
                        </div>
                    </div>
                </div>

                <p v-if="error" class="text-red-400 text-sm -mt-2">{{ error }}</p>

                <PrimaryButton class="w-full justify-center" :disabled="isLoading" @click="download">
                    <svg v-if="isLoading" class="animate-spin w-4 h-4 mr-2 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ isLoading ? 'A gerar…' : 'Descarregar ZIP' }}
                </PrimaryButton>
            </div>

            <p class="text-xs text-content-muted text-center">
                <template v-if="selectedIds.length > 0 && monthCount > 0">
                    {{ selectedIds.length }} {{ selectedIds.length === 1 ? 'ficheiro Excel' : 'ficheiros Excel' }}
                    · {{ monthCount }} {{ monthCount === 1 ? 'folha' : 'folhas' }} cada
                    · ZIP para download
                </template>
                <template v-else>
                    Seleciona colaboradores e o período para exportar um ZIP com um ficheiro Excel por pessoa,
                    com uma folha por mês.
                </template>
            </p>
        </div>
    </AppLayout>
</template>

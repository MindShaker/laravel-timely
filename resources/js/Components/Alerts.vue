<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const success = ref(page.props.flash?.success ?? null);
const error   = ref(page.props.flash?.error   ?? null);
const status  = ref(page.props.flash?.status  ?? null);

watch(() => page.props.flash, (flash) => {
    success.value = flash?.success ?? null;
    error.value   = flash?.error   ?? null;
    status.value  = flash?.status  ?? null;
}, { deep: true });
</script>

<template>
    <div>
        <div v-if="success" class="flex items-center p-4 mb-4 text-sm rounded-lg bg-surface text-green-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg>
            <span class="ms-3 font-medium">{{ success }}</span>
            <button @click="success = null" class="ms-auto p-1.5 rounded-lg hover:bg-neutral-700 inline-flex h-8 w-8 items-center justify-center">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
            </button>
        </div>

        <div v-if="error" class="flex items-center p-4 mb-4 text-sm rounded-lg bg-surface text-red-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm1 11H9v-2h2v2Zm0-4H9v-4h2v4Z"/></svg>
            <span class="ms-3 font-medium">{{ error }}</span>
            <button @click="error = null" class="ms-auto p-1.5 rounded-lg hover:bg-neutral-700 inline-flex h-8 w-8 items-center justify-center">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
            </button>
        </div>

        <div v-if="status" class="flex items-center p-4 mb-4 text-sm rounded-lg bg-surface text-amber-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 5a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm1 10H9v-6h2v6Z"/></svg>
            <span class="ms-3 font-medium">{{ status }}</span>
            <button @click="status = null" class="ms-auto p-1.5 rounded-lg hover:bg-neutral-700 inline-flex h-8 w-8 items-center justify-center">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
            </button>
        </div>
    </div>
</template>

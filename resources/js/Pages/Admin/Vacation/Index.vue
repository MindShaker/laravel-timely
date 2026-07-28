<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { inject } from 'vue';

const props = defineProps({
    year:       Number,
    rows:       Array,
    monthNames: Object,
    allowance:  Number,
    prevYear:   Number,
    nextYear:   Number,
});

const route = inject('route');
</script>

<template>
    <AppLayout>
        <Head :title="`Férias ${year}`" />

        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="text-lg font-semibold text-content">Visão geral de férias</h2>

                <div class="flex items-center gap-2">
                    <Link :href="route('admin.vacation', prevYear)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <span class="text-base font-semibold text-content w-12 text-center">{{ year }}</span>
                    <Link :href="route('admin.vacation', nextYear)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-base text-left px-4 py-3 text-xs font-semibold text-content-muted uppercase tracking-wider border-b border-r border-neutral-700 min-w-40">
                            Colaborador
                        </th>
                        <th v-for="m in 12" :key="m"
                            class="px-3 py-3 text-center text-xs font-semibold text-content-muted uppercase tracking-wider border-b border-neutral-700 min-w-14">
                            {{ monthNames[m] }}
                        </th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-content-muted uppercase tracking-wider border-b border-l border-neutral-700 min-w-16">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, i) in rows" :key="row.id"
                        class="group hover:bg-surface-hover transition-colors">
                        <td class="sticky left-0 z-10 bg-base group-hover:bg-surface-hover transition-colors px-4 py-2.5 font-medium text-content border-b border-r border-neutral-800 whitespace-nowrap">
                            {{ row.name }}
                        </td>
                        <td v-for="m in 12" :key="m"
                            class="px-3 py-2.5 text-center border-b border-neutral-800 tabular-nums">
                            <span v-if="row.months[m]"
                                class="inline-flex items-center justify-center size-7 rounded-md text-xs font-semibold"
                                :class="row.total > allowance ? 'bg-amber-900/40 text-amber-300' : 'bg-cyan-900/40 text-cyan-300'">
                                {{ row.months[m] }}
                            </span>
                            <span v-else class="text-neutral-700">—</span>
                        </td>
                        <td class="px-3 py-2.5 text-center border-b border-l border-neutral-800 tabular-nums">
                            <span v-if="row.total"
                                class="inline-flex items-center justify-center min-w-8 px-2 h-7 rounded-md text-xs font-bold"
                                :class="row.total > allowance
                                    ? 'bg-amber-900/60 text-amber-200'
                                    : row.total === allowance
                                        ? 'bg-cyan-900/60 text-cyan-200'
                                        : 'bg-neutral-800 text-content-muted'">
                                {{ row.total }}/{{ allowance }}
                            </span>
                            <span v-else class="text-neutral-700">—</span>
                        </td>
                    </tr>

                    <!-- Column totals -->
                    <tr class="bg-surface">
                        <td class="sticky left-0 z-10 bg-surface px-4 py-2.5 text-xs font-semibold text-content-muted uppercase tracking-wider border-t border-r border-neutral-700">
                            Total
                        </td>
                        <td v-for="m in 12" :key="m"
                            class="px-3 py-2.5 text-center border-t border-neutral-700 tabular-nums">
                            <span class="text-xs font-semibold text-content-muted">
                                {{ rows.reduce((s, r) => s + (r.months[m] || 0), 0) || '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 text-center border-t border-l border-neutral-700 tabular-nums">
                            <span class="text-xs font-bold text-content">
                                {{ rows.reduce((s, r) => s + r.total, 0) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

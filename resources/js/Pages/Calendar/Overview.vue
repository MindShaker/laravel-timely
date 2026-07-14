<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const props = defineProps({
    year:       Number,
    months:     Object,
    monthNames: Object,
    yearTotals: Object,
    today:      String,
});

const route = inject('route');

const typeConfig = {
    vacation:  { label: 'Férias',     color: '#22d3ee', bg: '#164e63' },
    client:    { label: 'Cliente',    color: '#22c55e', bg: '#14532d' },
    internal:  { label: 'Interno',    color: '#1d4ed8', bg: '#172554' },
    undefined: { label: 'Disponível', color: '#f97316', bg: '#7c2d12' },
    training:  { label: 'Formação',   color: '#a855f7', bg: '#3b0764' },
    absent:    { label: 'Ausente',    color: '#f43f5e', bg: '#4c0519' },
};

const typeKeys = Object.keys(typeConfig);

const grandTotal = computed(() =>
    typeKeys.reduce((sum, t) => sum + (props.yearTotals[t]?.days ?? 0), 0)
);

const [todayYear, todayMonth] = props.today.split('-').map(Number);

const monthEntries = computed(() =>
    Object.entries(props.months).map(([m, data]) => {
        const mNum = Number(m);
        return {
            m: mNum,
            name: props.monthNames[mNum],
            total: data.total,
            types: data.types,
            isPast:    props.year < todayYear || (props.year === todayYear && mNum < todayMonth),
            isCurrent: props.year === todayYear && mNum === todayMonth,
        };
    })
);
</script>

<template>
    <AppLayout>
        <Head :title="`Visão Anual ${year}`" />

        <template #header>
            <div class="grid grid-cols-3 items-center gap-3">

                <div class="flex items-center gap-3">
                    <Link :href="route('calendar.overview', year - 1)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <h2 class="text-lg font-semibold text-content text-center w-28">{{ year }}</h2>
                    <Link :href="route('calendar.overview', year + 1)"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div class="flex justify-center">
                    <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                        <Link :href="route('calendar')"
                            class="text-content-muted hover:text-content px-3 py-1.5 transition">
                            Mensal
                        </Link>
                        <Link :href="route('calendar.week')"
                            class="text-content-muted hover:text-content px-3 py-1.5 border-l border-neutral-700 transition">
                            Semanal
                        </Link>
                        <Link :href="route('calendar.overview', year)"
                            class="bg-surface-hover text-content font-medium px-3 py-1.5 border-l border-neutral-700 transition">
                            Anual
                        </Link>
                    </div>
                </div>

                <div></div>

            </div>
        </template>

        <div v-if="grandTotal > 0" class="mb-6">
            <div class="flex h-2.5 rounded-full overflow-hidden bg-neutral-800">
                <template v-for="t in typeKeys" :key="t">
                    <div v-if="yearTotals[t]?.days > 0"
                        :style="`background:${typeConfig[t].color};flex-grow:${yearTotals[t].days}`"
                        :title="`${typeConfig[t].label}: ${yearTotals[t].days} dias`">
                    </div>
                </template>
            </div>
        </div>

        <div class="flex gap-6 items-start">

            <!-- Sidebar -->
            <aside class="w-44 shrink-0 flex flex-col">

                <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">{{ year }}</p>

                <template v-for="t in typeKeys" :key="t">
                    <div v-if="yearTotals[t]?.days > 0"
                        class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-left mb-0.5">
                        <span class="size-2.5 rounded-sm shrink-0 ring-1"
                            :style="`background:${typeConfig[t].bg};--tw-ring-color:${typeConfig[t].color}60`"></span>
                        <span class="text-content-muted flex-1 text-sm leading-none">{{ typeConfig[t].label }}</span>
                        <span class="text-content text-xs tabular-nums font-medium">
                            {{ yearTotals[t].days }}d
                        </span>
                    </div>
                </template>

                <div class="border-t border-neutral-800 my-3"></div>

                <div class="flex items-center gap-2.5 px-2.5 py-2">
                    <span class="size-2.5 rounded-sm shrink-0 bg-neutral-700"></span>
                    <span class="text-content-muted flex-1 text-sm leading-none">Total</span>
                    <span class="text-content text-xs tabular-nums font-semibold">{{ grandTotal }}d</span>
                </div>

            </aside>

            <!-- Month grid -->
            <div class="flex-1 min-w-0">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <Link v-for="entry in monthEntries" :key="entry.m"
                :href="route('calendar.month', [year, entry.m])"
                class="group flex flex-col gap-3 rounded-xl border p-4 transition-all"
                :class="entry.isCurrent
                    ? 'border-neutral-600 bg-surface'
                    : 'border-neutral-800 bg-surface hover:border-neutral-600'">

                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold transition-colors"
                        :class="entry.isCurrent ? 'text-content' : 'text-content-muted group-hover:text-content'">
                        {{ entry.name }}
                    </h3>
                    <span v-if="entry.total > 0" class="text-[11px] text-neutral-500 tabular-nums">{{ entry.total }}d</span>
                </div>

                <div class="h-1.5 rounded-full overflow-hidden bg-neutral-800">
                    <div v-if="entry.total > 0" class="flex h-full">
                        <template v-for="t in typeKeys" :key="t">
                            <div v-if="entry.types[t]?.days > 0"
                                :style="`background:${typeConfig[t].color};flex-grow:${entry.types[t].days}`">
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 min-h-[3rem]">
                    <template v-if="entry.total > 0">
                        <template v-for="t in typeKeys" :key="t">
                            <div v-if="entry.types[t]?.days > 0" class="flex items-center gap-2 text-xs">
                                <span class="size-2 rounded-sm shrink-0" :style="`background:${typeConfig[t].color}`"></span>
                                <span class="text-content-muted flex-1 leading-none">{{ typeConfig[t].label }}</span>
                                <span class="font-medium text-content tabular-nums">
                                    {{ entry.types[t].days }}d / {{ entry.types[t].peopleCount }}p
                                </span>
                            </div>
                        </template>
                    </template>
                    <p v-else class="text-[11px] text-neutral-700 italic leading-none mt-1">Sem registos</p>
                </div>

            </Link>
        </div>
            </div><!-- /month grid -->

        </div><!-- /flex layout -->

    </AppLayout>
</template>

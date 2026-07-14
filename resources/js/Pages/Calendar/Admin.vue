<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { useCalendar } from '@/Composables/useCalendar';
import { Head, Link } from '@inertiajs/vue3';
import { computed, inject, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user:               Object,
    year:               Number,
    month:              Number,
    monthName:          String,
    daysInMonth:        Number,
    startOffset:        Number,
    totalWorkdays:      Number,
    yearStatusDays:     Array,
    holidays:           Object,
    birthdayDates:      Array,
    prevMonth:          Object,
    nextMonth:          Object,
    yearVacationCount:  Number,
    vacationAllowance:  Number,
    holidayCount:       Number,
    workedDays:         Number,
});

const route = inject('route');

const calendar = useCalendar({
    initialStatusDays: props.yearStatusDays,
    holidayDates: Object.keys(props.holidays),
    birthdayDates: props.birthdayDates,
    cacheKey: null,
    userId: null,
    routes: {
        markRange:   route('admin.calendar.range',       props.user.id),
        removeRange: route('admin.calendar.removeRange', props.user.id),
        removeDay:   (date) => route('admin.calendar.remove', [props.user.id, date]),
    },
});

onMounted(() => window.addEventListener('mouseup', calendar.endDrag));
onUnmounted(() => window.removeEventListener('mouseup', calendar.endDrag));

const yearVacationCount = computed(() =>
    calendar.statusDays.value.filter(s => s.type === 'vacation').length
);

const monthVacationCount = computed(() => {
    const prefix = `${String(props.year).padStart(4,'0')}-${String(props.month).padStart(2,'0')}`;
    return calendar.statusDays.value.filter(s => s.type === 'vacation' && s.date.startsWith(prefix)).length;
});

const workedDays = computed(() => Math.max(0, props.totalWorkdays - monthVacationCount.value));

const cells = computed(() => {
    const result = [];
    for (let i = 0; i < props.startOffset; i++) result.push({ empty: true });
    for (let day = 1; day <= props.daysInMonth; day++) {
        const date = `${props.year}-${String(props.month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        const dow = new Date(date + 'T12:00:00').getDay();
        result.push({
            empty: false, day, date,
            isWeekend:  dow === 0 || dow === 6,
            holName:    props.holidays[date] ?? null,
            isBirthday: props.birthdayDates.includes(date),
        });
    }
    return result;
});

const markTypes = [
    { key: 'vacation', label: 'Férias',     bg: '#164e63', bd: '#22d3ee' },
    { key: 'client',   label: 'Cliente',    bg: '#14532d', bd: '#22c55e' },
    { key: 'internal', label: 'Interno',    bg: '#172554', bd: '#1d4ed8' },
    { key: 'undefined',label: 'Disponível', bg: '#7c2d12', bd: '#f97316' },
    { key: 'training', label: 'Formação',   bg: '#3b0764', bd: '#a855f7' },
    { key: 'absent',   label: 'Ausente',    bg: '#4c0519', bd: '#f43f5e' },
];
</script>

<template>
    <AppLayout>
        <Head :title="`${monthName} ${year} — ${user.name}`" />

        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.users')" class="text-content-muted hover:text-content text-sm transition">← Utilizadores</Link>
                    <span class="text-neutral-600">/</span>
                    <span class="text-sm font-medium text-content">{{ user.name }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('admin.calendar.month', [user.id, prevMonth.year, prevMonth.month])"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <h2 class="text-lg font-semibold text-content capitalize text-center w-44">{{ monthName }} {{ year }}</h2>
                    <Link :href="route('admin.calendar.month', [user.id, nextMonth.year, nextMonth.month])"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div class="flex items-center gap-4 text-sm text-content-muted">
                    <span>
                        Férias:
                        <strong :class="yearVacationCount > vacationAllowance ? 'text-amber-400' : 'text-content'">
                            {{ yearVacationCount }}/{{ vacationAllowance }}
                        </strong>
                        <span v-if="yearVacationCount > vacationAllowance"
                            class="ml-1 text-[10px] font-semibold text-amber-500 bg-amber-900/30 px-1 py-0.5 rounded">
                            +{{ yearVacationCount - vacationAllowance }}
                        </span>
                    </span>
                    <span>Feriados: <strong class="text-content">{{ holidayCount }}</strong></span>
                    <span>Dias úteis: <strong class="text-content">{{ workedDays }}</strong></span>
                </div>
            </div>
        </template>

        <div class="select-none pb-10">
            <div class="flex gap-6 items-start">

                <!-- Sidebar -->
                <aside class="w-44 shrink-0 flex flex-col">

                    <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Marcar como</p>

                    <button v-for="t in markTypes" :key="t.key"
                        @click="calendar.markType.value = t.key"
                        class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm font-medium transition-all text-left mb-0.5"
                        :class="calendar.markType.value === t.key ? 'text-white' : 'text-content-muted hover:text-content hover:bg-surface-hover'"
                        :style="calendar.markType.value === t.key ? `background-color:${t.bg}` : ''">
                        <span class="size-2.5 rounded-sm shrink-0 ring-1 transition-all"
                            :style="`background:${t.bd};--tw-ring-color:${t.bd}60`"></span>
                        {{ t.label }}
                    </button>

                    <button @click="calendar.markRemote.value = !calendar.markRemote.value"
                        class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm font-medium transition-all text-left mt-1"
                        :class="calendar.markRemote.value ? 'text-content bg-neutral-700' : 'text-content-muted hover:text-content hover:bg-surface-hover'">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Remoto
                    </button>

                    <div class="border-t border-neutral-800 my-4"></div>

                    <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Mostrar</p>

                    <button v-for="t in markTypes" :key="t.key"
                        @click="calendar.filters.value[t.key] = !calendar.filters.value[t.key]"
                        class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                        :class="calendar.filters.value[t.key] ? 'text-content' : 'text-neutral-600'">
                        <span class="size-2.5 rounded-sm shrink-0 transition-all"
                            :style="calendar.filters.value[t.key] ? `background:${t.bd}` : `background:${t.bd}30`"></span>
                        <span :class="!calendar.filters.value[t.key] ? 'line-through' : ''">{{ t.label }}</span>
                    </button>
                </aside>

                <!-- Calendar -->
                <div class="flex-1 min-w-0">

                    <div class="grid grid-cols-7 mb-1">
                        <div v-for="dow in ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb']" :key="dow"
                            class="text-center text-xs font-medium py-2 text-content-muted uppercase tracking-wide">
                            {{ dow }}
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        <template v-for="(cell, i) in cells" :key="i">
                            <div v-if="cell.empty"></div>
                            <div v-else
                                class="relative rounded-lg border aspect-3/2 flex flex-col items-center justify-center text-sm transition"
                                :class="[
                                    cell.isWeekend  ? 'border-neutral-800 opacity-40 cursor-default' : '',
                                    cell.holName    ? 'border-amber-800/40 bg-amber-950/30 cursor-not-allowed' : '',
                                    cell.isBirthday ? 'border-amber-500/40 bg-amber-900/20 cursor-not-allowed' : '',
                                    (!cell.isWeekend && !cell.holName && !cell.isBirthday) ? 'border-neutral-700 bg-surface cursor-pointer hover:border-primary/50 hover:bg-surface-hover' : '',
                                ]"
                                v-bind="(!cell.isWeekend && !cell.holName && !cell.isBirthday) ? {
                                    onMousedown: (e) => { e.preventDefault(); calendar.startDrag(cell.date); },
                                    onMouseover: () => calendar.updateDrag(cell.date),
                                    style: calendar.cellStyle(cell.date),
                                } : {}">

                                <span class="font-medium">{{ cell.day }}</span>
                                <span v-if="cell.holName"
                                    class="text-[10px] text-amber-400 text-center leading-tight px-1 mt-0.5 line-clamp-2">
                                    {{ cell.holName }}
                                </span>
                                <span v-if="cell.isBirthday" class="text-[10px] text-amber-300 mt-0.5">🎂</span>
                            </div>
                        </template>
                    </div>

                    <p class="text-center text-xs text-content-muted mt-6">
                        Selecione o tipo e arraste para marcar férias de <strong>{{ user.name }}</strong>
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

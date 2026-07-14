<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { useCalendar } from '@/Composables/useCalendar';
import { Head, Link } from '@inertiajs/vue3';
import { computed, inject, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
    year: Number,
    month: Number,
    monthName: String,
    daysInMonth: Number,
    startOffset: Number,
    totalWorkdays: Number,
    yearStatusDays: Array,
    holidays: Object,
    birthdayDates: Array,
    prevMonth: Object,
    nextMonth: Object,
    yearVacationCount: Number,
    vacationAllowance: Number,
    holidayCount: Number,
    workedDays: Number,
    teamMembers: { type: Array, default: () => [] },
    othersStatus: { type: Object, default: () => ({}) },
    othersBirthdays: { type: Object, default: () => ({}) },
});

const route = inject('route');

const calendar = useCalendar({
    initialStatusDays: props.yearStatusDays,
    holidayDates: Object.keys(props.holidays),
    birthdayDates: props.birthdayDates,
    cacheKey: `timely_status_${props.user.id}_${props.year}`,
    userId: props.user.id,
    allPeopleIds: [props.user.id, ...props.teamMembers.map(m => m.id)],
    initialSelectedPeople: { [props.user.id]: true },
    routes: {
        markRange: route('calendar.range'),
        removeRange: route('calendar.removeRange'),
        removeDay: (date) => route('calendar.remove', date),
    },
});

onMounted(() => window.addEventListener('mouseup', calendar.endDrag));
onUnmounted(() => window.removeEventListener('mouseup', calendar.endDrag));

// ── Stats ─────────────────────────────────────────────────────────────────────

const yearVacationCount = computed(() =>
    calendar.statusDays.value.filter(s => s.type === 'vacation').length
);

const monthVacationCount = computed(() => {
    const prefix = `${String(props.year).padStart(4, '0')}-${String(props.month).padStart(2, '0')}`;
    return calendar.statusDays.value.filter(s => s.type === 'vacation' && s.date.startsWith(prefix)).length;
});

const workedDays = computed(() => Math.max(0, props.totalWorkdays - monthVacationCount.value));

// ── Calendar grid ─────────────────────────────────────────────────────────────

const cells = computed(() => {
    const result = [];
    for (let i = 0; i < props.startOffset; i++) result.push({ empty: true });
    for (let day = 1; day <= props.daysInMonth; day++) {
        const date = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dow = new Date(date + 'T12:00:00').getDay();
        result.push({
            empty: false, day, date,
            isWeekend: dow === 0 || dow === 6,
            holName: props.holidays[date] ?? null,
            isBirthday: props.birthdayDates.includes(date),
        });
    }
    return result;
});

// ── Others per cell ───────────────────────────────────────────────────────────

const badgeStyles = {
    vacation: { bg: 'bg-cyan-900/40', text: 'text-cyan-300' },
    client: { bg: 'bg-emerald-900/40', text: 'text-emerald-300' },
    internal: { bg: 'bg-blue-900/40', text: 'text-blue-300' },
    undefined: { bg: 'bg-orange-900/40', text: 'text-orange-300' },
    training: { bg: 'bg-purple-900/40', text: 'text-purple-300' },
    absent: { bg: 'bg-rose-900/40', text: 'text-rose-300' },
};

function othersForDate(date) {
    return (props.othersStatus[date] ?? []).filter(o =>
        calendar.filters.value[o.type] && calendar.selectedPeople.value[o.user_id]
    );
}

function birthdaysForDate(date) {
    return props.othersBirthdays[date] ?? [];
}

function groupByType(arr) {
    return arr.reduce((acc, o) => { (acc[o.type] = acc[o.type] || []).push(o); return acc; }, {});
}

const markTypes = [
    { key: 'vacation', label: 'Férias', bg: '#164e63', bd: '#22d3ee' },
    { key: 'client', label: 'Cliente', bg: '#14532d', bd: '#22c55e' },
    { key: 'internal', label: 'Interno', bg: '#172554', bd: '#1d4ed8' },
    { key: 'undefined', label: 'Disponível', bg: '#7c2d12', bd: '#f97316' },
    { key: 'training', label: 'Formação', bg: '#3b0764', bd: '#a855f7' },
    { key: 'absent', label: 'Ausente', bg: '#4c0519', bd: '#f43f5e' },
];

const typeLabels = {
    vacation: 'Férias', client: 'Cliente', internal: 'Interno',
    undefined: 'Disponível', training: 'Formação', absent: 'Ausente',
};
</script>

<template>
    <AppLayout>

        <Head :title="`${monthName} ${year}`" />

        <template #header>
            <div class="grid grid-cols-3 items-center gap-3">

                <div class="flex items-center gap-3">
                    <Link :href="route('calendar.month', [prevMonth.year, prevMonth.month])"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <h2 class="text-lg font-semibold text-content capitalize text-center w-44">{{ monthName }} {{ year
                        }}</h2>
                    <Link :href="route('calendar.month', [nextMonth.year, nextMonth.month])"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div class="flex justify-center">
                    <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                        <Link :href="route('calendar.month', [year, month])"
                            class="bg-surface-hover text-content font-medium px-3 py-1.5 transition">
                            Mensal
                        </Link>
                        <Link :href="route('calendar.week')"
                            class="text-content-muted hover:text-content px-3 py-1.5 border-l border-neutral-700 transition">
                            Semanal
                        </Link>
                        <Link :href="route('calendar.overview', year)"
                            class="text-content-muted hover:text-content px-3 py-1.5 border-l border-neutral-700 transition">
                            Anual
                        </Link>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 text-sm text-content-muted">
                    <span class="flex items-center gap-1.5" :title="`Dias de férias em ${year}`">
                        <span class="size-3 rounded-sm bg-[#164e63] ring-1 ring-[#22d3ee] inline-block"></span>
                        Férias:
                        <strong :class="yearVacationCount > vacationAllowance ? 'text-amber-400' : 'text-content'">
                            {{ yearVacationCount }}/{{ vacationAllowance }}
                        </strong>
                        <span v-if="yearVacationCount > vacationAllowance"
                            class="text-[10px] font-semibold text-amber-500 bg-amber-900/30 px-1 py-0.5 rounded">
                            +{{ yearVacationCount - vacationAllowance }}
                        </span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="size-3 rounded-sm bg-amber-900/30 ring-1 ring-amber-600/50 inline-block"></span>
                        Feriados: <strong class="text-content">{{ holidayCount }}</strong>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="size-3 rounded-sm bg-surface ring-1 ring-neutral-600 inline-block"></span>
                        Dias úteis: <strong class="text-content">{{ workedDays }}</strong>
                    </span>
                </div>

            </div>
        </template>

        <div class="select-none pb-10">
            <div class="flex gap-6 items-start">

                <!-- Sidebar -->
                <aside class="w-44 shrink-0 flex flex-col">

                    <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Marcar
                        como</p>

                    <button v-for="t in markTypes" :key="t.key" @click="calendar.markType.value = t.key"
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
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Remoto
                    </button>

                    <div class="border-t border-neutral-800 my-4"></div>

                    <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Mostrar
                    </p>

                    <button v-for="t in markTypes" :key="t.key"
                        @click="calendar.filters.value[t.key] = !calendar.filters.value[t.key]"
                        class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                        :class="calendar.filters.value[t.key] ? 'text-content' : 'text-neutral-600'">
                        <span class="size-2.5 rounded-sm shrink-0 transition-all"
                            :style="calendar.filters.value[t.key] ? `background:${t.bd}` : `background:${t.bd}30`"></span>
                        <span :class="!calendar.filters.value[t.key] ? 'line-through' : ''">{{ t.label }}</span>
                    </button>

                    <template v-if="teamMembers.length">
                        <div class="border-t border-neutral-800 my-4"></div>
                        <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">
                            Pessoas</p>

                        <button @click="calendar.toggleAll()"
                            class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                            :class="calendar.allSelected.value ? 'text-content' : 'text-content-muted hover:text-content hover:bg-surface-hover'">
                            <span class="size-4 rounded border flex items-center justify-center shrink-0 transition-all"
                                :class="calendar.allSelected.value ? 'bg-neutral-500 border-neutral-500' : 'border-neutral-600'">
                                <svg v-if="calendar.allSelected.value" class="size-2.5" viewBox="0 0 10 10" fill="none"
                                    stroke="white" stroke-width="1.5" stroke-linecap="round">
                                    <path d="M1.5 5L4 7.5L8.5 2" />
                                </svg>
                            </span>
                            Todos
                        </button>

                        <button
                            @click="calendar.selectedPeople.value[user.id] = !calendar.selectedPeople.value[user.id]"
                            class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                            :class="calendar.selectedPeople.value[user.id] ? 'text-content' : 'text-content-muted hover:text-content hover:bg-surface-hover'">
                            <span class="size-4 rounded border flex items-center justify-center shrink-0 transition-all"
                                :class="calendar.selectedPeople.value[user.id] ? 'bg-neutral-500 border-neutral-500' : 'border-neutral-600'">
                                <svg v-if="calendar.selectedPeople.value[user.id]" class="size-2.5" viewBox="0 0 10 10"
                                    fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round">
                                    <path d="M1.5 5L4 7.5L8.5 2" />
                                </svg>
                            </span>
                            Eu
                        </button>

                        <button v-for="member in teamMembers" :key="member.id"
                            @click="calendar.selectedPeople.value[member.id] = !calendar.selectedPeople.value[member.id]"
                            class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                            :class="calendar.selectedPeople.value[member.id] ? 'text-content' : 'text-content-muted hover:text-content hover:bg-surface-hover'">
                            <span class="size-4 rounded border flex items-center justify-center shrink-0 transition-all"
                                :class="calendar.selectedPeople.value[member.id] ? 'bg-neutral-500 border-neutral-500' : 'border-neutral-600'">
                                <svg v-if="calendar.selectedPeople.value[member.id]" class="size-2.5"
                                    viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="1.5"
                                    stroke-linecap="round">
                                    <path d="M1.5 5L4 7.5L8.5 2" />
                                </svg>
                            </span>
                            {{ member.name }}
                        </button>
                    </template>
                </aside>

                <!-- Calendar -->
                <div class="flex-1 min-w-0">

                    <div class="grid grid-cols-7 mb-1">
                        <div v-for="dow in ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']" :key="dow"
                            class="text-center text-xs font-medium py-2 text-content-muted uppercase tracking-wide">
                            {{ dow }}
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        <template v-for="(cell, i) in cells" :key="i">
                            <div v-if="cell.empty"></div>
                            <div v-else
                                class="group relative rounded-lg border aspect-3/2 flex flex-col items-center justify-center text-sm transition"
                                :class="[
                                    cell.isWeekend ? 'border-neutral-800 opacity-40 cursor-default' : '',
                                    cell.holName ? 'border-amber-800/40 bg-amber-950/30 cursor-not-allowed' : '',
                                    cell.isBirthday ? 'border-amber-500/40 bg-amber-900/20 cursor-not-allowed' : '',
                                    (!cell.isWeekend && !cell.holName && !cell.isBirthday) ? 'border-neutral-700 bg-surface cursor-pointer hover:border-primary/50 hover:bg-surface-hover' : '',
                                ]" v-bind="(!cell.isWeekend && !cell.holName && !cell.isBirthday) ? {
                                    onMousedown: (e) => { e.preventDefault(); calendar.startDrag(cell.date); },
                                    onMouseover: () => calendar.updateDrag(cell.date),
                                    style: calendar.cellStyle(cell.date),
                                } : {}">

                                <span class="font-medium">{{ cell.day }}</span>

                                <span v-if="cell.holName"
                                    class="text-[10px] text-amber-400 text-center leading-tight px-1 mt-0.5 line-clamp-2">
                                    {{ cell.holName }}
                                </span>
                                <span v-if="cell.isBirthday" class="text-[10px] text-amber-300 mt-0.5">🎂
                                    Aniversário</span>

                                <!-- Team member badges -->
                                <template v-if="teamMembers.length">
                                    <div v-if="othersForDate(cell.date).length"
                                        class="flex flex-wrap flex-col gap-1 justify-center mt-1 px-1">
                                        <span v-for="o in othersForDate(cell.date)" :key="o.user_id"
                                            class="text-[11px] px-1.5 py-0.5 rounded font-semibold leading-none"
                                            :class="[badgeStyles[o.type]?.bg ?? 'bg-orange-900/40', badgeStyles[o.type]?.text ?? 'text-orange-300', o.remote ? 'border border-dashed border-current' : '']"
                                            :title="o.name">{{ o.initials }}</span>
                                    </div>

                                    <div v-if="birthdaysForDate(cell.date).length"
                                        class="flex flex-wrap gap-1 justify-center mt-1 px-1">
                                        <span v-for="(b, bi) in birthdaysForDate(cell.date).slice(0, 5)" :key="bi"
                                            class="text-[11px] px-1.5 py-0.5 rounded bg-yellow-900/40 text-yellow-300 font-semibold leading-none"
                                            :title="b.name">{{ b.initials }} 👑</span>
                                        <span v-if="birthdaysForDate(cell.date).length > 5"
                                            class="text-[11px] px-1.5 py-0.5 rounded bg-neutral-800 text-neutral-400 leading-none">
                                            +{{ birthdaysForDate(cell.date).length - 5 }}
                                        </span>
                                    </div>

                                    <!-- Hover tooltip -->
                                    <div v-if="othersForDate(cell.date).length || birthdaysForDate(cell.date).length"
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-50
                                               pointer-events-none opacity-0 group-hover:opacity-100
                                               transition-opacity duration-150
                                               bg-neutral-900 border border-neutral-700 rounded-lg shadow-2xl
                                               p-2.5 min-w-[140px] whitespace-nowrap text-left">

                                        <template v-for="(people, type) in groupByType(othersForDate(cell.date))"
                                            :key="type">
                                            <p
                                                class="text-[10px] font-medium text-neutral-500 uppercase tracking-wide mb-1 mt-2 first:mt-0">
                                                {{ typeLabels[type] ?? type }}
                                            </p>
                                            <div v-for="o in people" :key="o.user_id"
                                                class="flex items-center gap-1.5 py-0.5">
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded font-semibold leading-none shrink-0"
                                                    :class="[badgeStyles[o.type]?.bg ?? 'bg-orange-900/40', badgeStyles[o.type]?.text ?? 'text-orange-300', o.remote ? 'border border-dashed border-current' : '']">
                                                    {{ o.initials }}
                                                </span>
                                                <span class="text-xs text-content">{{ o.name }}{{ o.remote ? ' 🏠' : ''
                                                    }}</span>
                                            </div>
                                        </template>

                                        <template v-if="birthdaysForDate(cell.date).length">
                                            <p class="text-[10px] font-medium text-neutral-500 uppercase tracking-wide mb-1"
                                                :class="othersForDate(cell.date).length ? 'mt-2' : ''">Aniversário</p>
                                            <div v-for="(b, bi) in birthdaysForDate(cell.date)" :key="bi"
                                                class="flex items-center gap-1.5 py-0.5">
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-yellow-900/40 text-yellow-300 font-semibold leading-none shrink-0">
                                                    {{ b.initials }} 👑
                                                </span>
                                                <span class="text-xs text-content">{{ b.name }}</span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>

                    <p class="text-center text-xs text-content-muted mt-6">
                        Selecione o tipo e arraste para marcar &nbsp;·&nbsp; Arraste num dia marcado para remover
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

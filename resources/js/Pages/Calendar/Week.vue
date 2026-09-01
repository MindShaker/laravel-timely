<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const props = defineProps({
    year: Number,
    week: Number,
    span: { type: Number, default: 1 },
    days: Array,
    holidays: Object,
    absenceGrid: Object,
    birthdayGrid: Object,
    users: Array,
    currentUser: Object,
    today: String,
    mondayYear: Number,
    mondayMonth: Number,
    prevWeek: Object,
    nextWeek: Object,
});

const route = inject('route');

const typeConfig = {
    vacation: { label: 'Férias', bg: '#164e63', color: '#22d3ee' },
    client: { label: 'Cliente', bg: '#14532d', color: '#22c55e' },
    internal: { label: 'Interno', bg: '#172554', color: '#1d4ed8' },
    undefined: { label: 'Disponível', bg: '#7c2d12', color: '#f97316' },
    training: { label: 'Formação', bg: '#3b0764', color: '#a855f7' },
    absent: { label: 'Ausente', bg: '#4c0519', color: '#f43f5e' },
};

const dayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
const monthShort = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

const weekRange = computed(() => {
    const [, sm, sd] = props.days[0].split('-').map(Number);
    const last = props.days[props.days.length - 1];
    const [ey, em, ed] = last.split('-').map(Number);
    return sm === em
        ? `${sd}–${ed} ${monthShort[em - 1]} ${ey}`
        : `${sd} ${monthShort[sm - 1]} – ${ed} ${monthShort[em - 1]} ${ey}`;
});

const allDayHeaders = computed(() => props.days.map((date, i) => {
    const dow = new Date(date + 'T12:00:00').getDay();
    return {
        date,
        label: dayLabels[i % 7],
        dayNum: Number(date.split('-')[2]),
        isWeekend: dow === 0 || dow === 6,
        isToday: date === props.today,
        isHol: !!props.holidays[date],
        holName: props.holidays[date] ?? null,
    };
}));

function isoWeekNum(dateStr) {
    const d = new Date(dateStr + 'T12:00:00');
    const day = d.getDay() || 7;
    d.setDate(d.getDate() + 4 - day);
    const yearStart = new Date(d.getFullYear(), 0, 1);
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

const weekGroups = computed(() => {
    if (props.span === 1) {
        return [{ weekNum: props.week, days: allDayHeaders.value }];
    }
    return [
        { weekNum: props.week, days: allDayHeaders.value.slice(0, 7) },
        { weekNum: isoWeekNum(props.days[7]), days: allDayHeaders.value.slice(7, 14) },
    ];
});

const weekTitle = computed(() => props.span === 2
    ? `Semanas ${props.week}–${isoWeekNum(props.days[7])}`
    : `Semana ${props.week}`
);

const spanUrl = (s) => route('calendar.week', [props.year, props.week]) + (s === 2 ? '?span=2' : '');
const prevUrl = () => route('calendar.week', [props.prevWeek.year, props.prevWeek.week]) + (props.span === 2 ? '?span=2' : '');
const nextUrl = () => route('calendar.week', [props.nextWeek.year, props.nextWeek.week]) + (props.span === 2 ? '?span=2' : '');

function getAbsence(userId, date) {
    return props.absenceGrid[userId]?.[date] ?? null;
}

function hasBirthday(userId, date) {
    return !!props.birthdayGrid[userId]?.[date];
}
</script>

<template>
    <AppLayout>

        <Head :title="`${weekTitle} — ${weekRange}`" />

        <template #header>
            <div class="grid grid-cols-3 items-center gap-3">

                <div class="flex items-center gap-3">
                    <Link :href="prevUrl()"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <div class="text-center">
                        <h2 class="text-lg font-semibold text-content leading-none">{{ weekTitle }}</h2>
                        <p class="text-xs text-content-muted mt-0.5">{{ weekRange }}</p>
                    </div>
                    <Link :href="nextUrl()"
                        class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div class="flex justify-center">
                    <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                        <Link :href="route('calendar.month', [mondayYear, mondayMonth])"
                            class="text-content-muted hover:text-content px-3 py-1.5 transition">
                            Mensal
                        </Link>
                        <Link :href="route('calendar.week', [year, week])"
                            class="bg-surface-hover text-content font-medium px-3 py-1.5 border-l border-neutral-700 transition">
                            Semanal
                        </Link>
                        <Link :href="route('calendar.overview', year)"
                            class="text-content-muted hover:text-content px-3 py-1.5 border-l border-neutral-700 transition">
                            Anual
                        </Link>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                        <Link :href="spanUrl(1)" class="px-3 py-1.5 transition"
                            :class="span === 1 ? 'bg-surface-hover text-content font-medium' : 'text-content-muted hover:text-content'">
                            1 sem
                        </Link>
                        <Link :href="spanUrl(2)" class="px-3 py-1.5 border-l border-neutral-700 transition"
                            :class="span === 2 ? 'bg-surface-hover text-content font-medium' : 'text-content-muted hover:text-content'">
                            2 sem
                        </Link>
                    </div>
                </div>

            </div>
        </template>

        <div class="overflow-x-auto rounded-xl border border-neutral-700">
            <table class="w-full table-fixed border-collapse min-w-[640px]">

                <colgroup>
                    <col style="width: 11rem">
                    <col v-for="(d, i) in allDayHeaders" :key="i" :style="d.isWeekend ? 'width: 4rem' : ''">
                </colgroup>

                <thead>
                    <!-- Week label row — only shown for 2-week view -->
                    <tr v-if="span === 2">
                        <th class="border-b border-r border-neutral-700 bg-surface"></th>
                        <th colspan="7"
                            class="text-center text-[10px] font-semibold text-neutral-500 uppercase tracking-widest py-2 border-b border-r-2 border-neutral-600 bg-surface">
                            Semana {{ week }}
                        </th>
                        <th colspan="7"
                            class="text-center text-[10px] font-semibold text-neutral-500 uppercase tracking-widest py-2 border-b border-neutral-700 bg-surface">
                            Semana {{ isoWeekNum(days[7]) }}
                        </th>
                    </tr>

                    <tr>
                        <th
                            class="w-44 min-w-44 text-left px-4 py-3 text-xs font-semibold text-content-muted uppercase tracking-widest border-b border-r border-neutral-700 bg-surface">
                            Pessoa
                        </th>
                        <th v-for="(d, i) in allDayHeaders" :key="d.date" class="text-center border-b" :class="[
                            i === 6 && span === 2 ? 'border-r-2 border-neutral-600' : (i < allDayHeaders.length - 1 ? 'border-r border-neutral-700' : 'border-neutral-700'),
                            d.isWeekend ? 'bg-neutral-900 px-1 py-3' : (d.isHol ? 'bg-amber-950/20 px-2 py-3' : 'bg-surface px-2 py-3'),
                        ]">
                            <span class="block text-[10px] font-semibold uppercase tracking-widest"
                                :class="d.isToday ? 'text-primary' : (d.isWeekend ? 'text-neutral-600' : 'text-content-muted')">
                                {{ d.label }}
                            </span>
                            <span class="block text-xl font-semibold mt-0.5"
                                :class="d.isToday ? 'text-primary' : (d.isWeekend ? 'text-neutral-600' : 'text-content')">
                                {{ d.dayNum }}
                            </span>
                            <span v-if="d.isHol"
                                class="block text-[9px] text-amber-400 leading-tight mt-0.5 truncate max-w-[80px] mx-auto"
                                :title="d.holName">{{ d.holName }}</span>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="u in users" :key="u.id"
                        class="border-b border-neutral-800 last:border-0 hover:bg-surface-hover/30 transition">

                        <td class="px-4 py-2.5 border-r border-neutral-700"
                            :class="u.id === currentUser.id ? 'bg-surface/50' : ''">
                            <template v-if="u.id === currentUser.id">
                                <span class="text-sm font-semibold text-content">Eu</span>
                                <span class="block text-xs text-content-muted leading-none mt-0.5">{{ currentUser.name
                                }}</span>
                            </template>
                            <span v-else class="text-sm text-content-muted">{{ u.name }}</span>
                        </td>

                        <td v-for="(d, i) in allDayHeaders" :key="d.date" class="py-1.5" :class="[
                            i === 6 && span === 2 ? 'border-r-2 border-neutral-600' : (i < allDayHeaders.length - 1 ? 'border-r border-neutral-800' : ''),
                            d.isWeekend ? 'bg-neutral-900/60 px-0.5' : (d.isHol ? 'bg-amber-950/10 px-1.5' : 'px-1.5'),
                        ]">
                            <div class="flex flex-col gap-1">
                                <template v-if="getAbsence(u.id, d.date)">
                                    <div class="rounded-md px-2.5 py-1.5 text-xs font-medium leading-snug"
                                        :class="getAbsence(u.id, d.date).remote ? 'border border-dashed' : ''"
                                        :style="`background:${typeConfig[getAbsence(u.id, d.date).type]?.bg};color:${typeConfig[getAbsence(u.id, d.date).type]?.color};${getAbsence(u.id, d.date).remote ? 'border-color:' + typeConfig[getAbsence(u.id, d.date).type]?.color + '70' : ''}`">
                                        <span class="block truncate">{{ typeConfig[getAbsence(u.id, d.date).type]?.label
                                        }}</span>
                                        <span v-if="getAbsence(u.id, d.date).remote"
                                            class="text-[10px] opacity-75">remoto</span>
                                    </div>
                                </template>
                                <template v-else-if="d.isHol && !d.isWeekend">
                                    <div class="rounded-md px-2.5 py-1.5 text-xs text-amber-400/70 bg-amber-950/20 leading-snug truncate"
                                        :title="d.holName">{{ d.holName }}</div>
                                </template>
                                <div v-if="hasBirthday(u.id, d.date)"
                                    class="rounded-md px-2.5 py-1 text-xs font-medium bg-yellow-900/30 text-yellow-300 leading-snug">
                                    🎂 Aniversário
                                </div>
                            </div>
                        </td>

                    </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>

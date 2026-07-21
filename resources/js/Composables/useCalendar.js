import { ref, computed, watch } from 'vue';

const TYPE_COLORS = {
    vacation: { bg: '#164e63', bd: '#22d3ee', pbg: 'rgba(22,78,99,0.55)',   pbd: 'rgba(34,211,238,0.6)' },
    client:   { bg: '#14532d', bd: '#22c55e', pbg: 'rgba(20,83,45,0.55)',   pbd: 'rgba(34,197,94,0.6)'  },
    internal: { bg: '#172554', bd: '#1d4ed8', pbg: 'rgba(23,37,84,0.55)',   pbd: 'rgba(29,78,216,0.6)'  },
    undefined:{ bg: '#7c2d12', bd: '#f97316', pbg: 'rgba(124,45,18,0.55)',  pbd: 'rgba(249,115,22,0.6)' },
    training: { bg: '#3b0764', bd: '#a855f7', pbg: 'rgba(59,7,100,0.55)',   pbd: 'rgba(168,85,247,0.6)' },
    absent:   { bg: '#4c0519', bd: '#f43f5e', pbg: 'rgba(76,5,25,0.55)',    pbd: 'rgba(244,63,94,0.6)'  },
};

/**
 * @param {object} opts
 * @param {Array}   opts.initialStatusDays   - [{ date, type, remote }] full year for this user
 * @param {Array}   opts.holidayDates        - ['YYYY-MM-DD'] current-month holidays
 * @param {Array}   opts.birthdayDates       - ['YYYY-MM-DD']
 * @param {string|null} opts.cacheKey        - localStorage key, or null to skip caching
 * @param {number|null} opts.userId          - viewed user's ID; null → skip selectedPeople check
 * @param {Array}   opts.allPeopleIds        - [userId, ...memberIds] for toggleAll
 * @param {object}  opts.initialSelectedPeople - initial selectedPeople map
 * @param {object}  opts.routes              - { markRange, removeRange, removeDay: (date)=>url }
 */
export function useCalendar({
    initialStatusDays,
    holidayDates,
    birthdayDates,
    cacheKey = null,
    selectedPeopleKey = null,
    userId = null,
    allPeopleIds = [],
    initialSelectedPeople = {},
    routes,
}) {
    const UI_PREF_KEY  = 'timely_ui_pref';

    function loadUiPref() {
        try { return JSON.parse(localStorage.getItem(UI_PREF_KEY)) ?? {}; } catch { return {}; }
    }
    function saveUiPref(patch) {
        try {
            const current = loadUiPref();
            localStorage.setItem(UI_PREF_KEY, JSON.stringify({ ...current, ...patch }));
        } catch {}
    }

    const uiPref = loadUiPref();

    const statusDays   = ref([]);
    const isDragging   = ref(false);
    const dragStart    = ref(null);
    const dragEnd      = ref(null);
    const dragActive   = ref(false);
    const dragMode     = ref('add');
    const dragStartType = ref(null);
    const markType     = ref(uiPref.markType ?? null);
    const markRemote   = ref(uiPref.markRemote ?? false);
    const filters      = ref(uiPref.filters ?? { vacation: true, client: true, internal: true, undefined: true, training: true, absent: true });
    const selectedPeople = ref({ ...initialSelectedPeople });
    const _statusIndex = ref({});
    const blockReason  = ref(null);
    let   _dragTimer   = null;
    let   _blockTimer  = null;

    function showBlock(msg) {
        clearTimeout(_blockTimer);
        blockReason.value = msg;
        _blockTimer = setTimeout(() => { blockReason.value = null; }, 3000);
    }

    // Initialise status days from cache or server data
    if (cacheKey) {
        try {
            const cached = localStorage.getItem(cacheKey);
            statusDays.value = cached ? JSON.parse(cached) : [...initialStatusDays];
            if (!cached) localStorage.setItem(cacheKey, JSON.stringify(statusDays.value));
        } catch {
            statusDays.value = [...initialStatusDays];
        }
    } else {
        statusDays.value = [...initialStatusDays];
    }

    // Initialise selected people from cache
    if (selectedPeopleKey) {
        try {
            const cached = localStorage.getItem(selectedPeopleKey);
            if (cached) selectedPeople.value = { ...initialSelectedPeople, ...JSON.parse(cached) };
        } catch {}
    }

    function rebuildIndex() {
        _statusIndex.value = Object.fromEntries(statusDays.value.map(s => [s.date, s]));
    }
    rebuildIndex();

    watch(statusDays, () => {
        rebuildIndex();
        if (cacheKey) {
            try { localStorage.setItem(cacheKey, JSON.stringify(statusDays.value)); } catch {}
        }
    }, { deep: true });

    watch(selectedPeople, () => {
        if (selectedPeopleKey) {
            try { localStorage.setItem(selectedPeopleKey, JSON.stringify(selectedPeople.value)); } catch {}
        }
    }, { deep: true });

    watch(markType,   v  => saveUiPref({ markType: v }));
    watch(markRemote, v  => saveUiPref({ markRemote: v }));
    watch(filters,    v  => saveUiPref({ filters: { ...v } }), { deep: true });

    // ── People ────────────────────────────────────────────────────────────────

    const allSelected = computed(() => allPeopleIds.length > 0 && allPeopleIds.every(id => selectedPeople.value[id]));

    function toggleAll() {
        const select = !allSelected.value;
        allPeopleIds.forEach(id => { selectedPeople.value[id] = select; });
    }

    // ── Preview ───────────────────────────────────────────────────────────────

    const preview = computed(() => {
        if (!isDragging.value || !dragStart.value || !dragEnd.value) return [];
        const a = new Date(dragStart.value + 'T00:00:00');
        const b = new Date(dragEnd.value + 'T00:00:00');
        const [from, to] = a <= b ? [a, b] : [b, a];
        const result = [];
        const cur = new Date(from);
        const fmt = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        while (cur <= to) {
            const d = fmt(cur);
            const dow = cur.getDay();
            if (dow !== 0 && dow !== 6 && !holidayDates.includes(d) && !birthdayDates.includes(d)) {
                if (dragMode.value === 'remove') {
                    const entry = _statusIndex.value[d];
                    if (!entry || entry.type !== dragStartType.value) { cur.setDate(cur.getDate() + 1); continue; }
                }
                result.push(d);
            }
            cur.setDate(cur.getDate() + 1);
        }
        return result;
    });

    function isInPreview(date) { return preview.value.includes(date); }

    // ── Cell style ────────────────────────────────────────────────────────────

    function cellStyle(date) {
        if (isInPreview(date)) {
            if (dragMode.value === 'remove') {
                return 'background-color:rgba(80,15,15,0.75);border-color:rgba(200,60,60,0.7);color:#ddd';
            }
            const c  = TYPE_COLORS[markType.value];
            const bs = markRemote.value ? 'dashed' : 'solid';
            return `background-color:${c.pbg};border-color:${c.pbd};border-style:${bs}`;
        }
        const entry = _statusIndex.value[date];
        const userVisible = userId === null || selectedPeople.value[userId];
        if (entry && filters.value[entry.type] && userVisible) {
            const c  = TYPE_COLORS[entry.type];
            const bs = entry.remote ? 'dashed' : 'solid';
            return `background-color:${c.bg};border-color:${c.bd};border-style:${bs};color:white`;
        }
        return null;
    }

    // ── Drag ──────────────────────────────────────────────────────────────────

    function startDrag(date) {
        clearTimeout(_dragTimer);
        const existing = _statusIndex.value[date];
        const visible  = existing && filters.value[existing.type] ? existing : null;
        // If a type is selected and the day is already marked with a *different* type, overwrite it.
        const mode     = visible && (!markType.value || visible.type === markType.value) ? 'remove' : 'add';

        // Block adding when no type is selected
        if (mode === 'add' && !markType.value) {
            showBlock('Seleciona um tipo antes de marcar dias.');
            return;
        }

        // Block when the current user is hidden in the sidebar
        if (userId !== null && !selectedPeople.value[userId]) {
            showBlock('Tens de estar selecionado nas Pessoas para marcar dias.');
            return;
        }

        isDragging.value    = true;
        dragActive.value    = false;
        dragMode.value      = mode;
        dragStartType.value = visible ? visible.type : null;
        dragStart.value     = date;
        dragEnd.value       = date;
        _dragTimer = setTimeout(() => { dragActive.value = true; }, 80);
    }

    function updateDrag(date) {
        if (!isDragging.value || !dragActive.value) return;
        dragEnd.value = date;
    }

    // Merge server response without clobbering concurrent optimistic updates.
    // Only dates within [rangeStart, rangeEnd] are reconciled from the server;
    // everything outside that window keeps the current (possibly optimistic) state.
    function reconcile(serverDays, rangeStart, rangeEnd) {
        const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const opRange = new Set();
        const cur = new Date(rangeStart + 'T00:00:00');
        const to  = new Date(rangeEnd   + 'T00:00:00');
        while (cur <= to) { opRange.add(fmt(cur)); cur.setDate(cur.getDate() + 1); }
        return [
            ...statusDays.value.filter(s => !opRange.has(s.date)),
            ...serverDays.filter(s => opRange.has(s.date)),
        ];
    }

    async function endDrag() {
        if (!isDragging.value) return;
        clearTimeout(_dragTimer);

        const days  = preview.value;
        const start = dragStart.value;
        const end   = dragEnd.value;
        const mode  = dragMode.value;
        const type  = dragStartType.value;

        isDragging.value = false;
        dragActive.value = false;
        dragStart.value  = dragEnd.value = null;

        if (days.length === 0) return;

        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        if (mode === 'remove') {
            statusDays.value = statusDays.value.filter(s => !(days.includes(s.date) && s.type === type));
            const res  = await fetch(routes.removeRange, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ start, end, type }),
            });
            const data = await res.json();
            statusDays.value = reconcile(data.status_days, start, end);
        } else {
            const mType   = markType.value;
            const mRemote = markRemote.value;
            statusDays.value = [
                ...statusDays.value.filter(s => !days.includes(s.date)),
                ...days.map(d => ({ date: d, type: mType, remote: mRemote })),
            ];
            const res  = await fetch(routes.markRange, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ start, end, type: mType, remote: mRemote }),
            });
            const data = await res.json();
            statusDays.value = reconcile(data.status_days, start, end);
        }
    }

    async function removeDay(date) {
        statusDays.value = statusDays.value.filter(s => s.date !== date);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res  = await fetch(routes.removeDay(date), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();
        statusDays.value = reconcile(data.status_days, date, date);
    }

    return {
        statusDays,
        isDragging,
        markType,
        markRemote,
        filters,
        selectedPeople,
        allSelected,
        blockReason,
        toggleAll,
        isInPreview,
        cellStyle,
        startDrag,
        updateDrag,
        endDrag,
        removeDay,
    };
}

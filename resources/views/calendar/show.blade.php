<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar.month', [$prevMonth->year, $prevMonth->month]) }}{{ $showAll ? '?all=1' : '' }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-content capitalize text-center w-44">
                    {{ $monthNames[$month] }} {{ $year }}
                </h2>
                <a href="{{ route('calendar.month', [$nextMonth->year, $nextMonth->month]) }}{{ $showAll ? '?all=1' : '' }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            {{-- Eu / Todos toggle --}}
            <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                <a href="{{ route('calendar.month', [$year, $month]) }}"
                    class="{{ !$showAll ? 'bg-surface-hover text-content font-medium' : 'text-content-muted hover:text-content' }} px-3 py-1.5 transition">
                    Eu
                </a>
                <a href="{{ route('calendar.month', [$year, $month]) }}?all=1"
                    class="{{ $showAll ? 'bg-surface-hover text-content font-medium' : 'text-content-muted hover:text-content' }} px-3 py-1.5 border-l border-neutral-700 transition">
                    Todos
                </a>
            </div>

            <div class="flex items-center gap-4 text-sm text-content-muted">
                <span x-data="{ yearCount: {{ $yearVacationCount }} }"
                    @vacation-updated.window="yearCount = $event.detail.filter(d => d.date.startsWith('{{ $year }}') && d.type === 'vacation').length"
                    class="flex items-center gap-1.5" title="Dias de férias em {{ $year }}">
                    <span class="size-3 rounded-sm bg-[#5c430a] ring-1 ring-[#d39a11] inline-block"></span>
                    Férias:
                    <strong :class="yearCount > {{ $vacationAllowance }} ? 'text-amber-400' : 'text-content'"
                        x-text="`${yearCount}/{{ $vacationAllowance }}`">{{ $yearVacationCount }}/{{ $vacationAllowance }}</strong>
                    <span x-show="yearCount > {{ $vacationAllowance }}"
                        x-text="`+${yearCount - {{ $vacationAllowance }}}`"
                        class="text-[10px] font-semibold text-amber-500 bg-amber-900/30 px-1 py-0.5 rounded"
                        style="{{ $yearVacationCount > $vacationAllowance ? '' : 'display:none' }}">+{{ $yearVacationCount - $vacationAllowance }}</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="size-3 rounded-sm bg-amber-900/30 ring-1 ring-amber-600/50 inline-block"></span>
                    Feriados: <strong class="text-content">{{ $holidayCount }}</strong>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="size-3 rounded-sm bg-surface ring-1 ring-neutral-600 inline-block"></span>
                    Dias úteis: <strong class="text-content">{{ $workedDays }}</strong>
                </span>
            </div>
        </div>
    </x-slot>

    @include('components.alerts')

    <div
        x-data="calendarApp()"
        @mouseup.window="endDrag()"
        class="select-none pb-10">

        <div class="flex gap-6 items-start">

        {{-- Sidebar --}}
        <aside class="w-44 shrink-0 flex flex-col">

            {{-- Mark type --}}
            <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Marcar como</p>

            @foreach([
                ['vacation', 'Férias',     '#5c430a', '#d39a11'],
                ['client',   'Cliente',    '#052e16', '#15803d'],
                ['internal', 'Interno',    '#172554', '#1d4ed8'],
                ['undefined','Disponível', '#431407', '#ea580c'],
            ] as [$t, $label, $bg, $bd])
            <button @click="markType = '{{ $t }}'"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm font-medium transition-all text-left mb-0.5"
                    :class="markType === '{{ $t }}' ? 'text-white' : 'text-content-muted hover:text-content hover:bg-surface-hover'"
                    :style="markType === '{{ $t }}' ? 'background-color:{{ $bg }}' : ''">
                <span class="size-2.5 rounded-sm shrink-0 ring-1 transition-all"
                      style="background:{{ $bd }};--tw-ring-color:{{ $bd }}60"></span>
                {{ $label }}
            </button>
            @endforeach

            <button @click="markRemote = !markRemote"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm font-medium transition-all text-left mt-1"
                    :class="markRemote ? 'text-content bg-neutral-700' : 'text-content-muted hover:text-content hover:bg-surface-hover'">
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Remoto
            </button>

            <div class="border-t border-neutral-800 my-4"></div>

            {{-- Visibility filters --}}
            <p class="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-1 mb-2">Mostrar</p>

            @foreach([
                ['vacation', 'Férias',     '#d39a11'],
                ['client',   'Cliente',    '#15803d'],
                ['internal', 'Interno',    '#1d4ed8'],
                ['undefined','Disponível', '#ea580c'],
            ] as [$t, $label, $bd])
            <button @click="filters['{{ $t }}'] = !filters['{{ $t }}']"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-sm transition-all text-left mb-0.5"
                    :class="filters['{{ $t }}'] ? 'text-content' : 'text-neutral-600'">
                <span class="size-2.5 rounded-sm shrink-0 transition-all"
                      :style="filters['{{ $t }}'] ? 'background:{{ $bd }}' : 'background:{{ $bd }}30'"></span>
                <span :class="!filters['{{ $t }}'] ? 'line-through' : ''">{{ $label }}</span>
            </button>
            @endforeach
        </aside>

        {{-- Calendar --}}
        <div class="flex-1 min-w-0">

        {{-- Day-of-week headers --}}
        <div class="grid grid-cols-7 mb-1">
            @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dow)
                <div class="text-center text-xs font-medium py-2 text-content-muted uppercase tracking-wide">{{ $dow }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7 gap-1">

            {{-- Leading empty cells --}}
            @for ($i = 0; $i < $startOffset; $i++)
                <div></div>
            @endfor

            {{-- Day cells --}}
            @php
            $badgeStyles = [
                'vacation' => ['bg' => 'bg-amber-900/40',  'text' => 'text-amber-300'],
                'client'   => ['bg' => 'bg-green-900/40',  'text' => 'text-green-300'],
                'internal' => ['bg' => 'bg-blue-900/40',   'text' => 'text-blue-300'],
                'undefined'=> ['bg' => 'bg-orange-900/40', 'text' => 'text-orange-300'],
            ];
            @endphp
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date      = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $dow       = (int) date('w', mktime(0, 0, 0, $month, $day, $year));
                    $isWeekend = $dow === 0 || $dow === 6;
                    $holName   = $holidays[$date] ?? null;
                    $isBirthday = in_array($date, $birthdayDates);
                    $isWorkday = !$isWeekend && !$holName && !$isBirthday;
                @endphp

                <div
                    data-date="{{ $date }}"
                    class="group relative rounded-lg border aspect-3/2 flex flex-col items-center justify-center text-sm transition
                        {{ $isWeekend ? 'border-neutral-800 opacity-40 cursor-default' : '' }}
                        {{ $holName ? 'border-amber-800/40 bg-amber-950/30 cursor-not-allowed' : '' }}
                        {{ $isBirthday ? 'border-amber-500/40 bg-amber-900/20 cursor-not-allowed' : '' }}
                        {{ $isWorkday ? 'border-neutral-700 bg-surface cursor-pointer hover:border-primary/50 hover:bg-surface-hover' : '' }}"
                    @if ($isWorkday) @mousedown.prevent="startDrag('{{ $date }}')"
                        @mouseover="updateDrag('{{ $date }}')"
                        :style="cellStyle('{{ $date }}')" @endif>
                    <span class="font-medium">{{ $day }}</span>

                    @if ($holName)
                        <span class="text-[10px] text-amber-400 text-center leading-tight px-1 mt-0.5 line-clamp-2">{{ $holName }}</span>
                    @endif
                    @if ($isBirthday)
                        <span class="text-[10px] text-amber-300 mt-0.5">🎂 Aniversário</span>
                    @endif

                    @if ($showAll && !empty($othersStatus[$date]))
                        <div class="flex flex-wrap gap-1 justify-center mt-1 px-1">
                            @foreach ($othersStatus[$date] as $other)
                                @php $bs = $badgeStyles[$other['type']] ?? $badgeStyles['undefined']; @endphp
                                <span x-show="filters['{{ $other['type'] }}']"
                                    class="text-[11px] px-1.5 py-0.5 rounded font-semibold leading-none {{ $bs['bg'] }} {{ $bs['text'] }} {{ $other['remote'] ? 'border border-dashed border-current' : '' }}"
                                    title="{{ $other['name'] }}">{{ $other['initials'] }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($showAll && !empty($othersBirthdays[$date]))
                        <div class="flex flex-wrap gap-1 justify-center mt-1 px-1">
                            @foreach (array_slice($othersBirthdays[$date], 0, 5) as $other)
                                <span class="text-[11px] px-1.5 py-0.5 rounded bg-yellow-900/40 text-yellow-300 font-semibold leading-none"
                                    title="{{ $other['name'] }}">{{ $other['initials'] }} 👑</span>
                            @endforeach
                            @if (count($othersBirthdays[$date]) > 5)
                                <span class="text-[11px] px-1.5 py-0.5 rounded bg-neutral-800 text-neutral-400 leading-none">
                                    +{{ count($othersBirthdays[$date]) - 5 }}
                                </span>
                            @endif
                        </div>
                    @endif

                    @if ($showAll && (!empty($othersStatus[$date]) || !empty($othersBirthdays[$date])))
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-50
                                    pointer-events-none opacity-0 group-hover:opacity-100
                                    transition-opacity duration-150
                                    bg-neutral-900 border border-neutral-700 rounded-lg shadow-2xl
                                    p-2.5 min-w-[140px] whitespace-nowrap text-left">
                            @if (!empty($othersStatus[$date]))
                                @php
                                    $typeLabels = ['vacation' => 'Férias', 'client' => 'Cliente', 'internal' => 'Interno', 'undefined' => 'Disponível'];
                                    $grouped = collect($othersStatus[$date])->groupBy('type');
                                @endphp
                                @foreach ($grouped as $type => $people)
                                    <div x-show="filters['{{ $type }}']">
                                        <p class="text-[10px] font-medium text-neutral-500 uppercase tracking-wide mb-1 mt-2 first:mt-0">{{ $typeLabels[$type] ?? $type }}</p>
                                        @foreach ($people as $other)
                                            @php $bs = $badgeStyles[$type] ?? $badgeStyles['undefined']; @endphp
                                            <div class="flex items-center gap-1.5 py-0.5">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold leading-none shrink-0 {{ $bs['bg'] }} {{ $bs['text'] }} {{ $other['remote'] ? 'border border-dashed border-current' : '' }}">{{ $other['initials'] }}</span>
                                                <span class="text-xs text-content">{{ $other['name'] }}{{ $other['remote'] ? ' 🏠' : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                            @if (!empty($othersBirthdays[$date]))
                                <p class="text-[10px] font-medium text-neutral-500 uppercase tracking-wide mb-1 {{ !empty($othersStatus[$date]) ? 'mt-2' : '' }}">Aniversário</p>
                                @foreach ($othersBirthdays[$date] as $other)
                                    <div class="flex items-center gap-1.5 py-0.5">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-yellow-900/40 text-yellow-300 font-semibold leading-none shrink-0">{{ $other['initials'] }} 👑</span>
                                        <span class="text-xs text-content">{{ $other['name'] }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
            @endfor
        </div>

        <p class="text-center text-xs text-content-muted mt-6">
            Selecione o tipo e arraste para marcar &nbsp;·&nbsp; Arraste num dia marcado para remover
        </p>

        </div>{{-- /.flex-1 calendar --}}
        </div>{{-- /.flex sidebar+calendar --}}
    </div>

    <script>
    function calendarApp() {
        return {
            statusDays:    @json($yearStatusDays),
            holidays:      @json(array_keys($holidays)),
            birthdays:     @json($birthdayDates),
            isDragging:    false,
            dragStart:     null,
            dragEnd:       null,
            dragActive:    false,
            dragMode:      'add',
            dragStartType: null,
            markType:      'vacation',
            markRemote:    false,
            filters:       { vacation: true, client: true, internal: true, undefined: true },
            _statusIndex:  {},

            init() {
                this._rebuildIndex();
                this.$watch('statusDays', () => this._rebuildIndex());
            },

            _rebuildIndex() {
                this._statusIndex = Object.fromEntries(this.statusDays.map(s => [s.date, s]));
            },

            _typeColors: {
                vacation: { bg: '#5c430a', bd: '#d39a11', pbg: 'rgba(92,67,10,0.55)',  pbd: 'rgba(211,154,17,0.6)' },
                client:   { bg: '#052e16', bd: '#15803d', pbg: 'rgba(5,46,22,0.55)',   pbd: 'rgba(21,128,61,0.6)'  },
                internal: { bg: '#172554', bd: '#1d4ed8', pbg: 'rgba(23,37,84,0.55)',  pbd: 'rgba(29,78,216,0.6)'  },
                undefined:{ bg: '#431407', bd: '#ea580c', pbg: 'rgba(67,20,7,0.55)',   pbd: 'rgba(234,88,12,0.6)'  },
            },

            get preview() {
                if (!this.isDragging || !this.dragStart || !this.dragEnd) return [];
                const a = new Date(this.dragStart + 'T00:00:00');
                const b = new Date(this.dragEnd   + 'T00:00:00');
                const [from, to] = a <= b ? [a, b] : [b, a];
                const result = [];
                const cur = new Date(from);
                const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                while (cur <= to) {
                    const d   = fmt(cur);
                    const dow = cur.getDay();
                    if (dow !== 0 && dow !== 6 && !this.holidays.includes(d) && !this.birthdays.includes(d)) {
                        if (this.dragMode === 'remove') {
                            const entry = this._statusIndex[d];
                            if (!entry || entry.type !== this.dragStartType) {
                                cur.setDate(cur.getDate() + 1);
                                continue;
                            }
                        }
                        result.push(d);
                    }
                    cur.setDate(cur.getDate() + 1);
                }
                return result;
            },

            isInPreview(date) { return this.preview.includes(date); },

            cellStyle(date) {
                if (this.isInPreview(date)) {
                    if (this.dragMode === 'remove') {
                        return 'background-color:rgba(80,15,15,0.75);border-color:rgba(200,60,60,0.7);color:#ddd';
                    }
                    const c  = this._typeColors[this.markType];
                    const bs = this.markRemote ? 'dashed' : 'solid';
                    return `background-color:${c.pbg};border-color:${c.pbd};border-style:${bs}`;
                }
                const entry = this._statusIndex[date];
                if (entry && this.filters[entry.type]) {
                    const c  = this._typeColors[entry.type];
                    const bs = entry.remote ? 'dashed' : 'solid';
                    return `background-color:${c.bg};border-color:${c.bd};border-style:${bs};color:white`;
                }
                return null;
            },

            startDrag(date) {
                clearTimeout(this._dragTimer);
                const existing = this._statusIndex[date];
                const visible  = existing && this.filters[existing.type] ? existing : null;
                this.isDragging    = true;
                this.dragActive    = false;
                this.dragMode      = visible ? 'remove' : 'add';
                this.dragStartType = visible ? visible.type : null;
                this.dragStart     = date;
                this.dragEnd       = date;
                this._dragTimer    = setTimeout(() => { this.dragActive = true; }, 80);
            },

            updateDrag(date) {
                if (!this.isDragging || !this.dragActive) return;
                this.dragEnd = date;
            },

            async endDrag() {
                if (!this.isDragging) return;

                clearTimeout(this._dragTimer);
                const days  = this.preview;
                const start = this.dragStart;
                const end   = this.dragEnd;
                const mode  = this.dragMode;
                const type  = this.dragStartType;
                this.isDragging = false;
                this.dragActive = false;
                this.dragStart  = this.dragEnd = null;

                if (days.length === 0) return;

                if (mode === 'remove') {
                    // Optimistic: filter out removed days of this type
                    this.statusDays = this.statusDays.filter(s => !(days.includes(s.date) && s.type === type));
                    this.$dispatch('vacation-updated', this.statusDays);

                    const response = await fetch('{{ route("calendar.removeRange") }}', {
                        method:  'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ start, end, type }),
                    });
                    const data = await response.json();
                    this.statusDays = data.status_days;
                    this.$dispatch('vacation-updated', this.statusDays);
                } else {
                    const markType   = this.markType;
                    const markRemote = this.markRemote;
                    // Optimistic: replace existing entries for these dates, add new ones
                    const untouched = this.statusDays.filter(s => !days.includes(s.date));
                    this.statusDays = [...untouched, ...days.map(d => ({ date: d, type: markType, remote: markRemote }))];
                    this.$dispatch('vacation-updated', this.statusDays);

                    const response = await fetch('{{ route("calendar.range") }}', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ start, end, type: markType, remote: markRemote }),
                    });
                    const data = await response.json();
                    this.statusDays = data.status_days;
                    this.$dispatch('vacation-updated', this.statusDays);
                }
            },

            async removeDay(date) {
                // Optimistic: remove immediately
                this.statusDays = this.statusDays.filter(s => s.date !== date);
                this.$dispatch('vacation-updated', this.statusDays);

                const response = await fetch(`{{ url('/calendar') }}/${date}`, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await response.json();
                this.statusDays = data.status_days;
                this.$dispatch('vacation-updated', this.statusDays);
            },
        };
    }
    </script>
</x-app-layout>

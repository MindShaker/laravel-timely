<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">

            {{-- Year navigation --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar.overview', $year - 1) }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-content text-center w-28">{{ $year }}</h2>
                <a href="{{ route('calendar.overview', $year + 1) }}"
                    class="p-1.5 rounded-lg hover:bg-surface-hover text-content-muted hover:text-content transition">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            {{-- View toggle --}}
            <div class="flex rounded-lg border border-neutral-700 overflow-hidden text-sm">
                <a href="{{ route('calendar') }}"
                    class="text-content-muted hover:text-content px-3 py-1.5 transition">
                    Mensal
                </a>
                <a href="{{ route('calendar.overview', $year) }}"
                    class="bg-surface-hover text-content font-medium px-3 py-1.5 border-l border-neutral-700 transition">
                    Anual
                </a>
            </div>

            {{-- Year totals legend --}}
            @php
                $typeConfig = [
                    'vacation' => ['label' => 'Férias',     'color' => '#22d3ee'],
                    'client'   => ['label' => 'Cliente',    'color' => '#22c55e'],
                    'internal' => ['label' => 'Interno',    'color' => '#1d4ed8'],
                    'undefined'=> ['label' => 'Disponível', 'color' => '#f97316'],
                    'training' => ['label' => 'Formação',   'color' => '#a855f7'],
                    'absent'   => ['label' => 'Ausente',    'color' => '#f43f5e'],
                ];
                $grandTotal = array_sum(array_column($yearTotals, 'days'));
            @endphp
            <div class="flex items-center gap-4 text-sm text-content-muted">
                @foreach ($typeConfig as $type => $cfg)
                    @if ($yearTotals[$type]['days'] > 0)
                        <span class="flex items-center gap-1.5">
                            <span class="size-2.5 rounded-sm inline-block" style="background:{{ $cfg['color'] }}"></span>
                            {{ $cfg['label'] }}:
                            <strong class="text-content">{{ $yearTotals[$type]['days'] }}</strong>
                            <span class="text-neutral-600 text-xs">({{ count($yearTotals[$type]['people']) }}p)</span>
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </x-slot>

    @include('components.alerts')

    @php
        $typeConfig = [
            'vacation' => ['label' => 'Férias',     'color' => '#22d3ee', 'bg' => '#164e63'],
            'client'   => ['label' => 'Cliente',    'color' => '#22c55e', 'bg' => '#14532d'],
            'internal' => ['label' => 'Interno',    'color' => '#1d4ed8', 'bg' => '#172554'],
            'undefined'=> ['label' => 'Disponível', 'color' => '#f97316', 'bg' => '#7c2d12'],
            'training' => ['label' => 'Formação',   'color' => '#a855f7', 'bg' => '#3b0764'],
            'absent'   => ['label' => 'Ausente',    'color' => '#f43f5e', 'bg' => '#4c0519'],
        ];
    @endphp

    {{-- Year stacked bar --}}
    @php $grandTotal = array_sum(array_column($yearTotals, 'days')); @endphp
    @if ($grandTotal > 0)
        <div class="mb-8">
            <div class="flex h-2.5 rounded-full overflow-hidden bg-neutral-800">
                @foreach ($typeConfig as $type => $cfg)
                    @if ($yearTotals[$type]['days'] > 0)
                        <div style="background:{{ $cfg['color'] }};flex-grow:{{ $yearTotals[$type]['days'] }}" title="{{ $cfg['label'] }}: {{ $yearTotals[$type]['days'] }} dias"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Month grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($months as $m => $month)
            @php
                $isPast = $year < now()->year || ($year === now()->year && $m < now()->month);
                $isCurrent = $year === now()->year && $m === now()->month;
            @endphp
            <a href="{{ route('calendar.month', [$year, $m]) }}"
                class="group flex flex-col gap-3 rounded-xl border p-4 transition-all
                {{ $isCurrent ? 'border-neutral-600 bg-surface' : 'border-neutral-800 bg-surface hover:border-neutral-600' }}">

                {{-- Month header --}}
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold {{ $isCurrent ? 'text-content' : 'text-content-muted group-hover:text-content' }} transition-colors">
                        {{ $monthNames[$m] }}
                    </h3>
                    @if ($month['total'] > 0)
                        <span class="text-[11px] text-neutral-500 tabular-nums">{{ $month['total'] }}d</span>
                    @endif
                </div>

                {{-- Stacked bar --}}
                <div class="h-1.5 rounded-full overflow-hidden bg-neutral-800">
                    @if ($month['total'] > 0)
                        <div class="flex h-full">
                            @foreach ($typeConfig as $type => $cfg)
                                @if ($month['types'][$type]['days'] > 0)
                                    <div style="background:{{ $cfg['color'] }};flex-grow:{{ $month['types'][$type]['days'] }}"></div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Type breakdown --}}
                <div class="flex flex-col gap-1.5 min-h-[3rem]">
                    @php $hasData = false; @endphp
                    @foreach ($typeConfig as $type => $cfg)
                        @if ($month['types'][$type]['days'] > 0)
                            @php $hasData = true; @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="size-2 rounded-sm shrink-0" style="background:{{ $cfg['color'] }}"></span>
                                <span class="text-content-muted flex-1 leading-none">{{ $cfg['label'] }}</span>
                                <span class="font-medium text-content tabular-nums">{{ $month['types'][$type]['days'] }} /
                                    {{ count($month['types'][$type]['people']) }}p</span>
                            </div>
                        @endif
                    @endforeach
                    @if (!$hasData)
                        <p class="text-[11px] text-neutral-700 italic leading-none mt-1">Sem registos</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

</x-app-layout>

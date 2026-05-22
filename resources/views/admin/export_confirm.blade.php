<x-app-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full bg-gray-800 p-8 shadow rounded-md">

            {{-- Icon + Title --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-12 w-12 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-2xl font-semibold text-gray-100">Incomplete Logs Detected</h2>
                <p class="mt-2 text-gray-400 text-sm">
                    The following days have no exit time recorded. Are you sure you want to export anyway?
                </p>
            </div>

            {{-- Incomplete logs list --}}
            <div class="mt-6 space-y-4 max-h-72 overflow-y-auto pr-1">
                @foreach ($incomplete as $personName => $days)
                    <div class="bg-gray-700 rounded-md px-4 py-3">
                        @if ($isAdmin)
                            <p class="text-sm font-semibold text-yellow-400 mb-2">{{ $personName }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            @foreach ($days as $day)
                                <span class="inline-block bg-gray-600 text-gray-200 text-xs font-medium px-2.5 py-1 rounded">
                                    {{ \Carbon\Carbon::parse($day)->format('d/m/Y') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Action buttons --}}
            <div class="mt-8 flex justify-center gap-4">
                {{-- Back to logs --}}
                <button type="button"
                    onclick="window.location='{{ $isAdmin ? route('adminlogs') : route('userlogs') }}'"
                    class="text-yellow-500 hover:text-yellow-100 border border-yellow-500 hover:bg-yellow-700
                           focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm
                           px-5 py-2.5 dark:focus:ring-yellow-900 transition cursor-pointer">
                    Back to Logs
                </button>

                {{-- Export anyway — re-submits original params + force=1 --}}
                <form method="GET" action="{{ $exportRoute }}">
                    @foreach ($params as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input type="hidden" name="force" value="1">
                    <button type="submit"
                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none
                               focus:ring-green-300 font-medium text-sm px-5 py-2.5
                               dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition cursor-pointer">
                        Export Anyway
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
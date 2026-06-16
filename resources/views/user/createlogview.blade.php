<x-app-layout>
    @if (session('message'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-surface dark:text-red-400" role="alert">
            <span class="font-medium">Danger alert!</span> {{ session('message') }}
        </div>
    @endif

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-surface dark:text-green-400" role="alert">
            <span class="font-medium">Success!</span> {{ session('success') }}
        </div>
    @endif

    @php
        $lunchStart = \Carbon\Carbon::parse(Auth::user()->inicio_almoco)->format('H:i');
        $lunchEnd   = \Carbon\Carbon::parse(Auth::user()->inicio_almoco)->addHour()->format('H:i');
    @endphp

    <div class="py-9">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('createlog') }}" method="post">
                @csrf
                <div class="grid gap-6 mb-6 md:grid-cols-2">

                    <div>
                        <label for="date" class="block mb-2 text-sm font-medium text-gray-100">Date</label>
                        <x-text-input type="date" name="data" id="date" value="{{ old('data', request('data')) }}" required />
                    </div>

                    <div>
                        <label for="entrada" class="block mb-2 text-sm font-medium text-gray-100">Entry</label>
                        <x-text-input type="time" id="entrada" name="entrada" value="{{ old('entrada', request('entrada')) }}" required />
                    </div>
                     <div>
                        <label for="final_almoco" class="block mb-2 text-sm font-medium text-gray-100">Lunch End</label>
                        <x-text-input type="time" id="final_almoco" name="final_almoco"
                            value="{{ old('final_almoco', $lunchEnd) }}" required />
                        <p class="mt-1 text-xs text-gray-400">
                            Your lunch starts at {{ $lunchStart }}. Default end is {{ $lunchEnd }} (1 hour).
                        </p>
                    </div>

                    <div>
                        <label for="left" class="block mb-2 text-sm font-medium text-gray-100">Left At</label>
                        <x-text-input type="time" id="left" name="saida" value="{{ old('saida', request('saida')) }}" required />
                    </div>

                   

                    <div class="md:col-span-2">
                        <label for="obs" class="block mb-2 text-sm font-medium text-gray-100">Obs</label>
                        <x-text-input id="obs" type="text" name="obs" value="{{ old('obs', request('obs')) }}" class="w-full" required />
                    </div>
                </div>

                <x-primary-app-button type="submit" style="cursor: pointer">SUBMIT</x-primary-app-button>
            </form>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    @if (session('message'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Danger alert!</span> {{ session('message') }}
        </div>
    @endif

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">Success!</span> {{ session('success') }}
        </div>
    @endif

    <div class="py-9">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('createlog') }}" method="post">
                @csrf
                <div class="grid gap-6 mb-6 md:grid-cols-2">

                    <div>
                        <label for="user_id" class="block mb-2 text-sm font-medium text-gray-100">User Name</label>
                        <select id="user_id" name="user_id" onchange="updateLunchInfo()"
                            class="bg-gray-700 border border-gray-600 text-gray-100 text-sm focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 placeholder-gray-400">
                            <option value="">Select a user...</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    data-lunch="{{ \Carbon\Carbon::parse($user->inicio_almoco)->format('H:i') }}"
                                    data-lunchend="{{ \Carbon\Carbon::parse($user->inicio_almoco)->addHour()->format('H:i') }}"
                                    {{ old('user_id', request('user_id')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

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
                            value="{{ old('final_almoco', request('final_almoco')) }}" required />
                        <p id="lunch-info" class="mt-1 text-xs text-gray-400">Select a user to see their lunch schedule.</p>
                    </div>

                    <div>
                        <label for="left" class="block mb-2 text-sm font-medium text-gray-100">Left At</label>
                        <x-text-input type="time" id="left" name="saida" value="{{ old('saida', request('saida')) }}" required />
                    </div>

                    

                    <div>
                        <label for="obs" class="block mb-2 text-sm font-medium text-gray-100">Obs</label>
                        <x-text-input id="obs" type="text" name="obs" value="{{ old('obs', request('obs')) }}" class="w-full" required />
                    </div>
                </div>

                <x-primary-app-button type="submit" style="cursor: pointer">SUBMIT</x-primary-app-button>
            </form>
        </div>
    </div>

    <script>
        function updateLunchInfo() {
            const select = document.getElementById('user_id');
            const option = select.options[select.selectedIndex];
            const lunchStart = option.dataset.lunch;
            const lunchEnd   = option.dataset.lunchend;
            const infoEl     = document.getElementById('lunch-info');
            const endInput   = document.getElementById('final_almoco');

            if (lunchStart && lunchEnd) {
                endInput.value = lunchEnd;
                infoEl.textContent = `Lunch starts at ${lunchStart}. Default end is ${lunchEnd} (1 hour).`;
            } else {
                endInput.value = '';
                infoEl.textContent = 'Select a user to see their lunch schedule.';
            }
        }

        // Run on page load in case a user is pre-selected (e.g. after validation error)
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('user_id');
            if (select.value) updateLunchInfo();
        });
    </script>
</x-app-layout>
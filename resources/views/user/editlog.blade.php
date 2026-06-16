<x-app-layout>
    @if (!empty($message))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-surface dark:text-red-400" role="alert">
            <span class="font-medium">Danger alert!</span> {{ $message }}
        </div>
    @endif
    <div class="py-9">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form action="{{ route('updateuserlog', ['logs' => $logs]) }}" method="post">
                @csrf
                @method('put')
                <div class="grid gap-6 mb-6 md:grid-cols-2">

                    <div>
                        <label for="date" class="block mb-2 text-sm font-medium text-gray-100">Date</label>
                        <x-text-input type="date" name="data" id="date" value="{{ $logs->data }}" required />
                    </div>

                    <div>
                        <label for="entry" class="block mb-2 text-sm font-medium text-gray-100">Entry</label>
                        <x-text-input type="time" id="entry" value="{{ $logs->entrada }}" name="entrada" required />
                    </div>
                     <div>
                        <label for="final_almoco" class="block mb-2 text-sm font-medium text-gray-100">Lunch End</label>
                        <x-text-input type="time" id="final_almoco"
                            value="{{ date('H:i', strtotime($logs->{'final_almoço'})) }}"
                            name="final_almoco" required />
                        <p class="mt-1 text-xs text-gray-400">Lunch starts at {{ date('H:i', strtotime($logs->user->inicio_almoco ?? '12:30')) }}. Change the end time to adjust the lunch duration.</p>
                    </div>

                    <div>
                        <label for="left" class="block mb-2 text-sm font-medium text-gray-100">Left At</label>
                        <x-text-input type="time" id="left" value="{{ $logs->saida }}" name="saida" required />
                    </div>

                   

                    <div class="md:col-span-2">
                        <label for="obs" class="block mb-2 text-sm font-medium text-gray-100">Obs</label>
                        <x-text-input type="text" id="obs" name="obs" value="{{ $logs->obs }}" placeholder="" required />
                    </div>

                </div>

                <div class="invisible">
                    <input type="hidden" value="{{ $logs->user_id }}" name="user_id">
                </div>
                <x-primary-app-button type="submit" style="cursor: pointer">SUBMIT</x-primary-app-button>
            </form>

        </div>
    </div>
</x-app-layout>
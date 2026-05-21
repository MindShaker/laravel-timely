<x-app-layout>
    @if (!empty($message))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Danger alert!</span> {{ $message }}
        </div>
    @endif
    <div class="py-9">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            


            <form action="{{ route('updatelog', ['logs' => $logs]) }}" method="post">
                @csrf
                @method('put')
                <div class="grid gap-6 mb-6 md:grid-cols-2">

                    <div>
                        <label for="date"
                            class="block mb-2 text-sm font-medium text-gray-100">Date</label>
                        <x-text-input type="date" name="data" id="date" value="{{ $logs->data }}"
                            required />
                    </div>
                    <div>
                        <label for="entry"
                            class="block mb-2 text-sm font-medium text-gray-100">Entry</label>
                        <x-text-input type="time" id="entry" min="08:00" value="{{ $logs->entrada }}" name="entrada" required />
                    </div>

                    <div>
                        <label for="left" class="block mb-2 text-sm font-medium text-gray-100">Left
                            At</label>
                        <x-text-input type="time" id="left" min="{{ $logs->entrada }}" value="{{ $logs->saida }}" name="saida"
                            required />
                    </div>

                    <div>
                        <label for="obs"
                            class="block mb-2 text-sm font-medium text-gray-100">Obs</label>
                        <x-text-input type="text" id="obs" name="obs" value="{{ $logs->obs }}"
                            placeholder="" required />
                    </div>
                </div>

                
                <div class="invisible">
                    <input type="hidden" value="{{ $logs->user_id }}" name="user_id">
                </div>
                <button type="submit" style="cursor: pointer"
                    class="text-yellow-100 hover:text-yellow-400 border border-yellow-400 hover:bg-inherit focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2 text-center  dark:border-yellow-300 text-yellow-300 hover:text-yellow-100 dark:hover:text-yellow-300 dark:hover:ring-yellow-900 dark:focus:bg-yellow-400">SUBMIT</button>
            </form>






        </div>
    </div>


</x-app-layout>

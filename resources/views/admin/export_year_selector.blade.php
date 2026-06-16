<x-app-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center bg-surface p-8 shadow rounded-md border border-gray-700">
            
            <!-- Calendar Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="h-12 w-12 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <h2 class="mt-6 text-2xl font-semibold text-gray-100">Annual Export</h2>
            
            <p class="mt-4 text-center text-gray-300">
                No name or month filters were applied. <br>
                Please select a <strong>year</strong> to generate the ZIP archive.
            </p>

            <!-- Selection Form -->
            <form method="GET" action="{{ $exportRoute }}" class="mt-8">
                <!-- Keep original parameters (format, force, etc) -->
                @foreach($params as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="flex flex-col items-center gap-4">
                    <div class="w-full">
                        <label for="year" class="block text-sm font-medium text-gray-400 mb-2">Select Year</label>
                        <select name="year" id="year" required
                            class="w-full bg-input border border-gray-600 text-gray-100 text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5">
                            @forelse($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @empty
                                <option value="">No logs found</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="mt-4 flex justify-center gap-4 w-full">
                        <!-- Back Button -->
                        <button type="button" onclick="window.history.back()"
                            class="flex-1 text-gray-400 hover:text-white border border-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-900 font-medium text-sm px-5 py-2.5 text-center cursor-pointer">
                            Cancel
                        </button>

                        <!-- Submit Button -->
                        <button type="submit" @if($availableYears->isEmpty()) disabled @endif
                            class="flex-1 text-yellow-500 hover:text-yellow-100 border border-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5 text-center dark:border-yellow-500 dark:hover:bg-yellow-600 dark:focus:ring-yellow-900 {{ $availableYears->isEmpty() ? 'opacity-50 cursor-not-allowed' : '' }} cursor-pointer">
                            Generate ZIP
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
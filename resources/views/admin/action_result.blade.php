<x-app-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center bg-white dark:bg-gray-800 p-8 shadow rounded-md">
            @if(isset($success) && $success)
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-12 w-12 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="mt-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Ação completa</h2>
            @else
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-12 w-12 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h2 class="mt-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Ação Processada</h2>
            @endif

            @if(isset($message))
                <p class="mt-4 text-gray-600 dark:text-gray-300">{{ $message }}</p>
                @if(isset($details) && is_array($details))
                    <div class="mt-4 text-sm text-gray-700 dark:text-gray-300 text-left">
                        @if(isset($details['status']))
                            <p><span class="font-semibold">Status:</span> {{ $details['status'] }}</p>
                        @endif
                        @if(isset($details['processed_by']))
                            <p><span class="font-semibold">Processed by:</span> {{ $details['processed_by'] }}</p>
                        @endif
                        @if(isset($details['processed_at']))
                            <p><span class="font-semibold">Processed at:</span> {{ $details['processed_at'] }}</p>
                        @endif
                    </div>
                @endif
            @endif

            <div class="mt-8 flex justify-center gap-4">
                <button type="button" onclick="window.location='{{ route('adminlogs') }}'" style="cursor: pointer" class="text-yellow-700 hover:text-white border border-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-yellow-500 dark:text-yellow-500 dark:hover:text-white dark:hover:bg-yellow-600 dark:focus:ring-yellow-900">
                    Go to logs
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-secondary-red-button type="submit">Logout</x-secondary-red-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

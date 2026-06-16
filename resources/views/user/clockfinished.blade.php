<x-app-layout>


    <div class="bg-surface min-h-screen">
        <div class="py-55">
            <div class="max-w-md mx-auto sm:px-4 lg:px-12">
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="max-w-lg p-6 bg-surface border border-yellow-400 shadow-sm text-center">
                        <a href="#">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-100">Your Day has
                                ended!</h5>
                        </a>
                        <p class="mb-3 font-normal text-gray-400">You started at: {{ $print_time = date('H:i', strtotime($logs->entrada)) }}
                        </p>
                        <p class="mb-3 font-normal text-gray-400">You left at: {{ $print_time = date('H:i', strtotime($logs->saida)) }}</p>
                        <p class="mb-3 font-normal text-gray-400">You did {{ $print_time = date('H:i', strtotime($logs->total_horas)) }}
                            hours </p>

                        <p class="mb-3 font-normal text-yellow-300">Thank you for logging your day.
                        </p>





                    </div>
                </div>
            </div>

</x-app-layout>

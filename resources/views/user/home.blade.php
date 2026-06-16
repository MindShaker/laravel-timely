<x-app-layout>

    <div class="bg-surface min-h-screen">
        <div class="py-50">
            <div class="max-w-md mx-auto sm:px-4 lg:px-12 ">
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="max-w p-6 bg-surface border border-yellow-400 shadow-sm text-center ">
                        <a href="#">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-100">TIMELY</h5>
                        </a>
                        <p class="mb-3 font-normal text-gray-400">Here you can keep track of how many
                            hours you do each day.</p>
                        <form action="{{ route('logcreate') }}" method="post">
                            @csrf
                            <x-primary-app-button>START</x-primary-app-button>
                        </form>



                    </div>
                </div>
            </div>
        </div>
</x-app-layout>

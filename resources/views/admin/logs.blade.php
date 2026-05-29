<x-app-layout>

    <div class="">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alerts />
            <div class="flex flex-row-reverse">
                <a href ="/admin/createlogview">
                    <x-primary-app-button>ADD</x-primary-app-button>
                </a>
            </div>

            <div class="bg-gray-800 overflow-hidden shadow-sm place-items-center">

                <div class="w-full p-6 text-gray-100 flex justify-between sm:flex flex-wrap">

                    <div>

                        <form action="/admin/logs" method="get">
                            @csrf
                            <div class="flex justify-between sm:flex flex-wrap">

                                <label for="table-search" class="sr-only">Search</label>

                                <div>
                                    <div class="">
                                        <select id="" name ="name"
                                            class="bg-gray-700 border border-gray-600 text-gray-100 text-sm focus:ring-yellow-400 focus:border-yellow-400 block w-full p-2.5 placeholder-gray-400">
                                            <option value="">ALL USERS</option>
                                            @foreach ($users->sortBy('name') as $user)
                                                <option value="{{ $user->name }}"
                                                    {{ request('name') == $user->name ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class ="ml-2">
                                    <x-text-input type="month" name="month" value="{{ request('month') }}" />
                                </div>
                                <div class ="ml-2">
                                    <input type="number" min="1" max="30" name="time" placeholder="DAY"
                                        value="{{ request('time') }}"
                                        class="bg-gray-700 border border-gray-600 text-gray-100 text-sm focus:ring-yellow-500 focus:border-yellow-500 p-2.5 w-24 appearance-none placeholder-gray-400" />
                                </div>


                                <div class="lg:pl-2">
                                    <x-secondary-app-button>SEARCH</x-secondary-app-button>
                                </div>
                                <div class="py-2">
                                    <a href="/admin/logs">
                                        <x-refresh-icon />
                                    </a>
                                </div>


                        </form>

                    </div>


                </div>
                <form action="{{ route('export') }}" method="get">
                    <input type="hidden" name ="name" value="{{ request('name') }}">
                    <input type="hidden" name ="month" value="{{ request('month') }}">
                    <input type="hidden" name ="time" value="{{ request('time') }}">
                    @csrf
                    <div class="ml-100 w-max flex space-between">

                        <div>
                            <select id="" name ="format"
                                class="bg-gray-700 border border-gray-600 text-gray-100 text-sm focus:ring-yellow-400 focus:border-yellow-400 block w-25 p-2.5 placeholder-gray-400">
                                <option value="xlsx">XLSX</option>


                            </select>
                        </div>
                        <div>
                            <x-secondary-app-button>
                                <x-export-icon />
                            </x-secondary-app-button>
                        </div>
                    </div>
                </form>

            </div>


            <div
                class="w-full relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-transparent">

                <table class="w-full text-sm text-left rtl:text-right text-body">
                    <thead class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-transparent">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Entry
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Lunch Start
                            </th>

                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Lunch End
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Exit
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Total Time
                            </th>
                            <th scope="col" class="px-6 py-3 font-large text-gray-100">
                                Manage
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="bg-neutral-primary border-b border-transparent">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-heading whitespace-nowrap text-gray-100">
                                    {{ $log->user->name }}
                                </th>
                                <td class="px-6 py-4 text-gray-100">
                                    {{ $log->data }}
                                </td>
                                <td class="px-6 py-4 text-gray-100">
                                    {{ $print_time = date('H:i', strtotime($log->entrada)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-100">
                                   {{ date('H:i', strtotime($log->user->inicio_almoco)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-100">
                                    {{ date('H:i', strtotime($log->final_almoço)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-100">
                                    {{ $print_time = date('H:i', strtotime($log->saida)) }}
                                </td>

                                <td class="px-6 py-4 text-gray-100">
                                    {{ $print_time = date('H:i', strtotime($log->total_horas)) }}
                                </td>
                                <td class="flex items-center px-4 py-4 text-gray-100">
                                    <a href ="/admin/looklog/{{ $log->id }}" class="mr-1">
                                        <x-eye-icon />

                                    </a>

                                    <a href ="/admin/editlog/{{ $log->id }}">
                                        <x-edit-icon />
                                    </a>
                                    <button command="show-modal" style="cursor: pointer"
                                        commandfor="dialog{{ $log->id }}">
                                        <x-trash-icon />
                                    </button>
                                </td>
                                <el-dialog>

                                    <dialog id="dialog{{ $log->id }}"
                                        aria-labelledby="dialog-title{{ $log->id }}"
                                        class="fixed inset-0 m-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent p-0 backdrop:bg-transparent">
                                        <el-dialog-backdrop
                                            class="fixed inset-0 bg-gray-900/50 transition-opacity data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in"></el-dialog-backdrop>

                                        <div tabindex="0"
                                            class="flex min-h-full items-end justify-center p-4 text-center focus:outline focus:outline-0 sm:items-center sm:p-0">
                                            <el-dialog-panel
                                                class="relative transform overflow-hidden bg-gray-800 text-left shadow-xl outline outline-1 -outline-offset-1 outline-gray-900/10 transition-all data-[closed]:translate-y-4 data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in sm:my-8 sm:max-w-lg data-[closed]:sm:translate-y-0 data-[closed]:sm:scale-95">
                                                <div class="bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">

                                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                                            <h3 id="dialog-title"
                                                                class="text-base font-semibold text-gray-100">Delete Log
                                                            </h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-400">Are you sure you want
                                                                    to delete this Log?</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-gray-700/25 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 justify-center items-center">

                                                    <form action ="/admin/delete/{{ $log->id }}" method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <x-primary-red-button command="close"
                                                            commandfor="dialog">Delete</x-primary-red-button>


                                                    </form>
                                                    <x-secondary-red-button type="button" command="close"
                                                        commandfor="dialog{{ $log->id }}">Cancel</x-secondary-red-button>

                                                </div>
                                            </el-dialog-panel>
                                        </div>
                                    </dialog>
                                </el-dialog>



                            </tr>
                        @endforeach
                    </tbody>

                </table>
                {{ $logs->links('pagination::tailwind') }}
            </div>


        </div>
    </div>

</x-app-layout>

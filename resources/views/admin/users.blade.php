<x-app-layout>

    <div class="">

        <div class=" max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alerts />
            <div class="flex flex-row-reverse">
                <a href ="/admin/createuserview">
                    <x-primary-app-button>ADD</x-primary-app-button>
                </a>
            </div>
            <div class="bg-gray-800 overflow-hidden shadow-sm place-items-center">
                <div class=" w-full p-6 text-gray-100 flex justify-between sm:flex flex-wrap">
                    <div class=" flex justify-between">
                        <div>
                            <form action="/admin/users" method="get">
                                @csrf
                                <x-text-input type="text" name="name" id="table-search"
                                    value="{{ request('name') }}" placeholder="Search name" />
                        </div>
                        <div class="pl-3">

                            <x-secondary-app-button>SEARCH</x-secondary-app-button>
                            </form>
                        </div>
                        <div class="py-2">
                            <a href="/admin/users">
                                <x-refresh-icon />
                            </a>
                        </div>

                    </div>
                    <form action="{{ route('exportusers') }}" method="get">
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
                        <thead
                            class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-transparent">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Name</th>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Email</th>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Finger</th>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Type</th>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Lunch</th>
                                <th scope="col" class="px-6 py-3 font-large text-gray-100">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="bg-neutral-primary border-b border-transparent">
                                    <th class="px-6 py-4 font-medium text-heading whitespace-nowrap text-gray-100">
                                        {{ $user->name }}
                                    </th>

                                    <td class="px-6 py-4 text-gray-100">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4" id="finger-cell-{{ $user->id }}">
                                        @if (!$user->finger)
                                            <button style="cursor:pointer" type="button"
                                                id="btn-enroll-{{ $user->id }}"
                                                onclick="startEnroll({{ $user->id }})" class="text-red-400">
                                                Enroll Finger
                                            </button>
                                        @else
                                            <div class="flex flex-col gap-0.5">
                                                <span class="badge bg-success text-yellow-400">✔ Active</span>
                                                <span
                                                    class="text-xs text-gray-400 font-semibold italic">{{ $user->chosen_finger ?? 'Right Thumb' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            class="{{ match ($user->tipo) {
                                                'admin' => 'text-yellow-400',
                                                'worker' => 'text-blue-400',
                                                default => 'text-gray-100',
                                            } }}">
                                            {{ $user->tipo }}
                                        </button>
                                    </td>

                                    <td class="px-6 py-4 text-gray-100">
                                        {{ $print_time = date('H:i', strtotime($user->inicio_almoco)) }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-100">
                                        @if (Auth::user()->tipo === 'admin')
                                            <div class="flex items-center gap-3">

                                                {{-- Botão de Editar (Sempre aparece) --}}
                                                <button command="show-modal" commandfor="dialog{{ $user->id }}"
                                                    style="cursor:pointer"
                                                    title="{{ $user->finger ? 'Edit Role / Remove Finger' : 'Edit Role' }}">

                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-6 text-gray-300 hover:text-white transition-colors">

                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />

                                                    </svg>
                                                </button>

                                                {{-- Botão do olho só aparece se tiver fingerprint --}}
                                                @if ($user->finger)
                                                    <button command="show-modal"
                                                        commandfor="dialog-finger{{ $user->id }}"
                                                        style="cursor:pointer" title="Manage Finger Choice">

                                                        <x-eye-icon />

                                                    </button>
                                                @endif

                                            </div>
                                        @endif
                                    </td>


                                    @if (Auth::user()->tipo === 'admin')
                                        <el-dialog>
                                            <dialog id="dialog{{ $user->id }}"
                                                aria-labelledby="dialog-title{{ $user->id }}"
                                                class="fixed inset-0 m-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent p-0 backdrop:bg-transparent">

                                                <el-dialog-backdrop
                                                    class="fixed inset-0 bg-gray-900/50 transition-opacity data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in">
                                                </el-dialog-backdrop>

                                                <div tabindex="0"
                                                    class="flex min-h-full items-end justify-center p-4 text-center focus:outline focus:outline-0 sm:items-center sm:p-0">

                                                    <el-dialog-panel
                                                        class="relative transform overflow-hidden bg-gray-800 text-left shadow-xl outline outline-1 -outline-offset-1 outline-gray-900/10 sm:my-8 sm:max-w-lg w-full">

                                                        {{-- HEADER --}}
                                                        <div class="bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">

                                                            <h3 id="dialog-title{{ $user->id }}"
                                                                class="text-base font-semibold text-gray-100 text-center">

                                                                Manage User: {{ $user->name }}

                                                            </h3>

                                                            <p class="text-sm text-gray-400 text-center mt-2">

                                                                @if (Auth::user()->id == $user->id)
                                                                    You can only remove your own fingerprint access.
                                                                    You cannot change your own role.
                                                                @else
                                                                    You can switch the user role
                                                                    @if ($user->finger)
                                                                        or remove their fingerprint access.
                                                                    @endif
                                                                @endif

                                                            </p>

                                                        </div>

                                                        {{-- BODY --}}
                                                        <div class="bg-gray-700/25 px-4 py-4 sm:px-6">

                                                            {{-- ===================== --}}
                                                            {{-- CASO 1: SEM FINGER --}}
                                                            {{-- ===================== --}}
                                                            @if (!$user->finger)
                                                                <div
                                                                    class="flex flex-col flex-row items-center justify-center gap-1">

                                                                    <button type="button" style="cursor: pointer"
                                                                        class="text-yellow-400 hover:!text-yellow-100 border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5"
                                                                        command="close"
                                                                        commandfor="dialog{{ $user->id }}">
                                                                        Cancel
                                                                    </button>

                                                                    @if (Auth::user()->id != $user->id)
                                                                        <form
                                                                            action="{{ route('changeusertype', $user->id) }}"
                                                                            method="POST"
                                                                            class=" items-center gap-2">

                                                                            @csrf
                                                                            @method('PUT')

                                                                            <select name="tipo"
                                                                                class="bg-gray-800 text-gray-100 border border-gray-600 px-8 py-2.5">

                                                                                <option value="user">User</option>
                                                                                <option value="worker">Worker</option>
                                                                                <option value="admin">Admin</option>

                                                                            </select>

                                                                            <x-primary-app-button type="submit">
                                                                                Switch
                                                                            </x-primary-app-button>

                                                                        </form>
                                                                    @endif

                                                                </div>

                                                                {{-- ===================== --}}
                                                                {{-- CASO 2: ADMIN SELF --}}
                                                                {{-- ===================== --}}
                                                            @elseif (Auth::user()->id == $user->id)
                                                                <div
                                                                    class="flex flex-row items-center justify-between">

                                                                    <button type="button" style="cursor: pointer"
                                                                        class="text-yellow-400 hover:!text-yellow-100 border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5"
                                                                        command="close"
                                                                        commandfor="dialog{{ $user->id }}">
                                                                        Cancel
                                                                    </button>

                                                                    <form
                                                                        action="{{ route('users.delete_finger', $user->id) }}"
                                                                        method="POST">
                                                                        @csrf

                                                                        <x-primary-red-button>
                                                                            Remove Fingerprint
                                                                        </x-primary-red-button>

                                                                    </form>

                                                                </div>

                                                                {{-- ===================== --}}
                                                                {{-- CASO 3: USER COM FINGER --}}
                                                                {{-- ===================== --}}
                                                            @else
                                                                <div
                                                                    class="flex flex-col items-center justify-center gap-3">

                                                                    <form
                                                                        action="{{ route('users.delete_finger', $user->id) }}"
                                                                        method="POST">
                                                                        @csrf

                                                                        <x-primary-red-button>
                                                                            Remove Fingerprint
                                                                        </x-primary-red-button>

                                                                    </form>

                                                                    <div
                                                                        class="flex flex-row items-center justify-center">

                                                                        <button type="button" style="cursor: pointer"
                                                                            class="text-yellow-400 hover:!text-yellow-100 border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5"
                                                                            command="close"
                                                                            commandfor="dialog{{ $user->id }}">
                                                                            Cancel
                                                                        </button>

                                                                        @if (Auth::user()->id != $user->id)
                                                                            <form
                                                                                action="{{ route('changeusertype', $user->id) }}"
                                                                                method="POST"
                                                                                class="flex flex-row items-center gap-2">

                                                                                @csrf
                                                                                @method('PUT')

                                                                                <select name="tipo"
                                                                                    class="bg-gray-800 text-gray-100 border border-gray-600 px-8 py-3 ml-1 mr-1">

                                                                                    <option value="user">User
                                                                                    </option>
                                                                                    <option value="worker">Worker
                                                                                    </option>
                                                                                    <option value="admin">Admin
                                                                                    </option>

                                                                                </select>

                                                                                <x-primary-app-button type="submit">
                                                                                    Switch
                                                                                </x-primary-app-button>

                                                                            </form>
                                                                        @endif

                                                                    </div>

                                                                </div>
                                                            @endif

                                                        </div>

                                                    </el-dialog-panel>

                                                </div>

                                            </dialog>
                                        </el-dialog>
                                    @endif


                                    {{-- MODAL 2: Escolha do Dedo (Apenas para utilizadores ativos) --}}
                                    @if (Auth::user()->tipo === 'admin' && $user->finger)
                                        <el-dialog>
                                            <dialog id="dialog-finger{{ $user->id }}"
                                                aria-labelledby="dialog-finger-title{{ $user->id }}"
                                                class="fixed inset-0 m-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent p-0 backdrop:bg-transparent justify-center">

                                                <el-dialog-backdrop
                                                    class="fixed inset-0 bg-gray-900/50 transition-opacity data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in"></el-dialog-backdrop>

                                                <div tabindex="0"
                                                    class="flex min-h-full items-end justify-center p-4 text-center focus:outline focus:outline-0 sm:items-center sm:p-0">

                                                    <el-dialog-panel
                                                        class="relative transform overflow-hidden bg-gray-800 text-left shadow-xl outline outline-1 -outline-offset-1 outline-gray-900/10 transition-all data-[closed]:translate-y-4 data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in sm:my-8 sm:max-w-lg w-full data-[closed]:sm:translate-y-0 data-[closed]:sm:scale-95">

                                                        <div class="bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                                            <div class="sm:flex sm:items-start">
                                                                <div
                                                                    class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                                                    <h3 id="dialog-finger-title{{ $user->id }}"
                                                                        class="text-base font-semibold text-gray-100">
                                                                        Fingerprint Assignment: {{ $user->name }}
                                                                    </h3>
                                                                    <div class="mt-2">
                                                                        <p class="text-sm text-gray-400">
                                                                            Select which finger corresponds to this
                                                                            user's active registration.
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="bg-gray-700/25 px-4 py-4 flex flex-col sm:flex-row items-center justify-center gap-1 sm:px-6">

                                                            <button type="button" style="cursor: pointer"
                                                                class="text-yellow-400 hover:!text-yellow-100 border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium text-sm px-5 py-2.5 text-center dark:border-yellow-500 dark:text-yellow-300 dark:hover:!text-yellow-100 dark:hover:bg-yellow-400 dark:focus:ring-yellow-900"
                                                                command="close"
                                                                commandfor="dialog-finger{{ $user->id }}">
                                                                Cancel
                                                            </button>

                                                            <form
                                                                action="{{ route('users.update_finger_choice', $user->id) }}"
                                                                method="POST"
                                                                class="flex flex-col sm:flex-row items-center w-full sm:w-auto">
                                                                @csrf
                                                                @method('PUT')

                                                                <select name="chosen_finger"
                                                                    class="w-full sm:w-auto bg-gray-800 text-gray-100 border border-gray-600 px-8 py-2 mr-2 text-base font-medium focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 focus:outline-none transition-colors h-[46px] leading-tight cursor-pointer">
                                                                    <option value="Right Thumb"
                                                                        {{ ($user->chosen_finger ?? 'Right Thumb') === 'Right Thumb' ? 'selected' : '' }}>
                                                                        Right Thumb</option>
                                                                    <option value="Left Thumb"
                                                                        {{ ($user->chosen_finger ?? '') === 'Left Thumb' ? 'selected' : '' }}>
                                                                        Left Thumb</option>
                                                                    <option value="Right Index"
                                                                        {{ ($user->chosen_finger ?? '') === 'Right Index' ? 'selected' : '' }}>
                                                                        Right Index</option>
                                                                    <option value="Left Index"
                                                                        {{ ($user->chosen_finger ?? '') === 'Left Index' ? 'selected' : '' }}>
                                                                        Left Index</option>
                                                                </select>

                                                                <x-primary-app-button type="submit">
                                                                    Save
                                                                </x-primary-app-button>
                                                            </form>
                                                        </div>

                                                    </el-dialog-panel>
                                                </div>
                                            </dialog>
                                        </el-dialog>
                                    @endif

                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                    @if (!request('name'))
                        {{ $users->links('pagination::tailwind') }}
                    @endif
                </div>

            </div>
        </div>
    </div>

</x-app-layout>

<script>
    function startEnroll(userId) {
        const btn = document.getElementById(`btn-enroll-${userId}`);
        const cell = document.getElementById(`finger-cell-${userId}`);

        if (!btn) return;

        btn.disabled = true;
        btn.innerText = "A aguardar sensor...";
        btn.classList.add('animate-pulse');

        fetch(`/users/${userId}/enroll`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log("MQTT Enviado. A iniciar verificação na DB...");

                const checkInterval = setInterval(() => {
                    fetch(`/admin/users/${userId}/finger-status`)
                        .then(res => res.json())
                        .then(statusData => {
                            console.log("Estado atual do dedo:", statusData.finger);

                            if (statusData.finger == 1) {
                                console.log("Sucesso! Dedo detetado.");
                                clearInterval(checkInterval);
                                cell.innerHTML = `
                                    <div class="flex flex-col gap-0.5">
                                        <span class="badge bg-success text-yellow-400">✔ Active</span>
                                        <span class="text-xs text-gray-400 font-semibold italic">Right Thumb</span>
                                    </div>
                                `;
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        })
                        .catch(err => console.error("Erro ao consultar status:", err));
                }, 2000);
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerText = "Enroll Finger";
            });
    }
</script>

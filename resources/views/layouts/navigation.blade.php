<nav x-data="{ open: false }" class="overflow-visible bg-base border border-neutral-700 rounded-xl max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 py-[3px] text-nav-fg">
    <div class="flex h-14 justify-between">

        <!-- Left: logo + nav links -->
        <div class="flex gap-0">
            <div class="flex items-center pl-5 pr-5 rounded-l-xl bg-base">
                <a href="{{ route('home') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-8">
                        <path fill-rule="evenodd"
                            d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <div class="hidden sm:flex items-center gap-1">
                <x-nav-link :href="route('userlogs')" :active="request()->routeIs('userlogs')">
                    {{ __('My Logs') }}
                </x-nav-link>
                @if (Auth::user()->tipo == 'admin')
                    <x-nav-link :href="route('adminlogs')" :active="request()->routeIs('adminlogs')">
                        {{ __('Manage Logs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.adminlogs')" :active="request()->routeIs('admin.adminlogs')">
                        {{ __('Admin Logs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('userlist')" :active="request()->routeIs('userlist')">
                        {{ __('Manage Users') }}
                    </x-nav-link>
                @endif
            </div>
        </div>

        <!-- Right: user link + mobile menu -->
        <div class="flex items-center gap-2 pl-3 pr-4 rounded-r-xl bg-base">
            <div class="hidden sm:flex items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-nav-fg hover:text-primary transition duration-150 ease-in-out">
                    {{ Auth::user()->name }}
                </a>
                <a href="{{ route('profile.edit') }}" class="text-nav-fg hover:text-primary transition duration-150 ease-in-out">
                    <x-pencil-icon />
                </a>
            </div>

            <!-- Hamburger -->
            <button @click="open = ! open" class="sm:hidden p-2 rounded-lg text-nav-fg hover:bg-nav-hover-bg transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" class="sm:hidden border-t border-neutral-700 py-2">
        <div class="flex flex-col gap-1 pb-1">
            <x-nav-link :href="route('userlogs')" :active="request()->routeIs('userlogs')">
                {{ __('My Logs') }}
            </x-nav-link>
            @if (Auth::user()->tipo == 'admin')
                <x-nav-link :href="route('adminlogs')" :active="request()->routeIs('adminlogs')">
                    {{ __('Manage Logs') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.adminlogs')" :active="request()->routeIs('admin.adminlogs')">
                    {{ __('Admin Logs') }}
                </x-nav-link>
                <x-nav-link :href="route('userlist')" :active="request()->routeIs('userlist')">
                    {{ __('Manage Users') }}
                </x-nav-link>
            @endif
            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                {{ __('Profile') }}
            </x-nav-link>
        </div>
    </div>
</nav>

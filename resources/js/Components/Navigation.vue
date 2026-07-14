<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const open = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.tipo === 'admin');

function isActive(routePattern) {
    const current = page.url;
    if (routePattern === 'calendar') return current.startsWith('/calendar') || current === '/';
    if (routePattern === 'admin.users') return current.startsWith('/admin/users');
    if (routePattern === 'admin.holidays') return current.startsWith('/admin/holidays');
    if (routePattern === 'admin.export') return current.startsWith('/admin/export');
    if (routePattern === 'profile.edit') return current.startsWith('/profile');
    return false;
}
</script>

<template>
    <nav
        class="overflow-visible bg-base border rounded-b-none border-neutral-700 rounded-t-xl max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 py-[3px] text-nav-fg">
        <div class="flex h-14 justify-between">

            <!-- Left: logo + nav links -->
            <div class="flex gap-0">
                <div class="flex items-center pl-5 pr-5 rounded-l-xl bg-base">
                    <Link :href="route('calendar')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-8">
                            <path fill-rule="evenodd"
                                d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z"
                                clip-rule="evenodd" />
                        </svg>
                    </Link>
                </div>

                <div class="hidden sm:flex items-center gap-1">
                    <Link :href="route('calendar')"
                        :class="isActive('calendar')
                            ? 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out'
                            : 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out'">
                        Calendário
                    </Link>
                    <template v-if="isAdmin">
                        <Link :href="route('admin.users')"
                            :class="isActive('admin.users')
                                ? 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out'">
                            Utilizadores
                        </Link>
                        <Link :href="route('admin.holidays', new Date().getFullYear())"
                            :class="isActive('admin.holidays')
                                ? 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out'">
                            Feriados
                        </Link>
                        <Link :href="route('admin.export')"
                            :class="isActive('admin.export')
                                ? 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out'">
                            Exportar
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Right: user + hamburger -->
            <div class="flex items-center gap-2 pl-3 pr-4 rounded-r-xl bg-base">
                <div class="hidden sm:flex items-center gap-3">
                    <Link :href="route('profile.edit')"
                        class="text-sm font-medium text-nav-fg hover:text-primary transition duration-150 ease-in-out">
                        {{ user?.name }}
                    </Link>
                    <Link :href="route('profile.edit')"
                        class="text-nav-fg hover:text-primary transition duration-150 ease-in-out">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        </svg>
                    </Link>
                </div>

                <button @click="open = !open"
                    class="sm:hidden p-2 rounded-lg text-nav-fg hover:bg-nav-hover-bg transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div v-show="open" class="sm:hidden border-t border-neutral-700 py-2">
            <div class="flex flex-col gap-1 pb-1">
                <Link :href="route('calendar')"
                    class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition">
                    Férias</Link>
                <template v-if="isAdmin">
                    <Link :href="route('admin.users')"
                        class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition">
                        Utilizadores</Link>
                    <Link :href="route('admin.holidays', new Date().getFullYear())"
                        class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition">
                        Feriados</Link>
                    <Link :href="route('admin.export')"
                        class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition">
                        Exportar</Link>
                </template>
                <Link :href="route('profile.edit')"
                    class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition">
                    Perfil</Link>
            </div>
        </div>
    </nav>
</template>

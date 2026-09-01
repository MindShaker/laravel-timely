<script setup>
import Navigation from '@/Components/Navigation.vue';
import Alerts from '@/Components/Alerts.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen bg-background relative">
        <div class="relative z-10 pt-3">
            <Navigation />

            <header v-if="$slots.header"
                class="mt-0 bg-base border border-neutral-700 border-t-0 rounded-b-xl px-4 py-4 sm:px-6 lg:px-8 text-nav-fg max-w-[1570px] mx-auto">
                <slot name="header" />
            </header>

            <main>
                <div class="max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    <Alerts />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

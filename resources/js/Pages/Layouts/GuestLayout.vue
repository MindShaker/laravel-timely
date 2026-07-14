<script setup>
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-background">
        <div>
            <a href="/">
                <img src="/images/timely.png" alt="Timely" class="w-60 sm:w-32 md:w-40 lg:w-60 h-auto max-w-full">
            </a>
        </div>
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-surface border border-neutral-700 shadow-md overflow-hidden sm:rounded-xl">
            <slot />
        </div>
    </div>
</template>

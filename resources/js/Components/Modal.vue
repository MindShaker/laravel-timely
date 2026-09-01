<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    maxWidth: { type: String, default: '2xl' },
});

defineEmits(['close']);

const maxWidthClass = computed(() => ({
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth]));

watch(() => props.show, (val) => {
    document.body.classList.toggle('overflow-y-hidden', val);
}, { immediate: true });
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" @keydown.escape.window="$emit('close')">
                <div class="fixed inset-0 bg-gray-900/75" @click="$emit('close')" />
                <div class="mb-6 bg-surface rounded-lg overflow-hidden shadow-xl sm:w-full sm:mx-auto relative z-10" :class="maxWidthClass">
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

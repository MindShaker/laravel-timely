<script setup>
import { ref, onMounted } from 'vue';

defineProps({
    modelValue: String,
    type: { type: String, default: 'text' },
    disabled: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        v-bind="$attrs"
        :type="type"
        :value="modelValue"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        class="rounded-md border-input-border bg-input text-content text-sm placeholder:text-input-placeholder shadow-sm px-3 py-2 focus:border-input-ring focus:ring-input-ring focus:outline-none"
    />
</template>

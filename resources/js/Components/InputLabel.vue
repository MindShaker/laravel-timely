<script setup>
defineProps({ value: String, for: String });
</script>

<template>
    <label :for="$props.for" class="block font-medium text-sm text-content-muted">
        {{ value ?? '' }}<slot />
    </label>
</template>

<script setup>
defineProps({ message: String });
</script>

<template>
    <p v-if="message" class="text-sm text-priority-urgent">{{ message }}</p>
</template>

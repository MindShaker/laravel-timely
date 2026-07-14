<script setup>
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({});
const logout = useForm({});

function submit() { form.post(route('verification.send')); }
function doLogout() { logout.post(route('logout')); }
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Email" />

        <p class="mb-4 text-sm text-content-muted">
            Obrigado por se registar! Verifique o seu email clicando no link que enviámos. Se não recebeu, podemos reenviar.
        </p>

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-green-400">
            Um novo link de verificação foi enviado para o seu email.
        </div>

        <div class="mt-4 flex items-center justify-between">
            <PrimaryButton @click="submit" :disabled="form.processing">Reenviar Email</PrimaryButton>
            <button @click="doLogout" class="text-sm text-content-muted underline hover:text-content">Sair</button>
        </div>
    </GuestLayout>
</template>

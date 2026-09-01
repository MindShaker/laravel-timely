<script setup>
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '' });

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Password" />

        <p class="mb-4 text-sm text-content-muted">
            Esqueceu a sua password? Indique o seu email e enviaremos um link para criar uma nova.
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full p-3" required autofocus />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">Enviar link</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

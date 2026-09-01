<script setup>
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

function submit() {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar Password" />

        <p class="mb-4 text-sm text-content-muted">
            Esta é uma área segura. Por favor confirme a sua password para continuar.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full p-3" required autocomplete="current-password" />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>
            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">Confirmar</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

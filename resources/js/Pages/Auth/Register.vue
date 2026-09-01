<script setup>
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Registar" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nome" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full p-3" required autofocus autocomplete="name" />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full p-3" required autocomplete="username" />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full p-3" required autocomplete="new-password" />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Password" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full p-3" required autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex flex-wrap items-center justify-end gap-4">
                <Link :href="route('login')" class="text-sm text-content-muted underline hover:text-content">Já tem conta?</Link>
                <PrimaryButton :disabled="form.processing">REGISTAR</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

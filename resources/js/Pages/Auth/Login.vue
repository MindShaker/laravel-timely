<script setup>
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full p-3" required autofocus autocomplete="username" />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full p-3" required autocomplete="current-password" />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="flex items-center">
                <input id="remember" v-model="form.remember" type="checkbox" class="rounded border-neutral-600 bg-neutral-800 text-primary">
                <label for="remember" class="ms-2 text-sm text-content-muted">Lembrar-me</label>
            </div>

            <div class="flex items-center justify-between">
                <Link v-if="$page.props.ziggy?.routes?.['password.request']" :href="route('password.request')" class="text-sm text-content-muted underline hover:text-content">
                    Esqueceu a password?
                </Link>
                <PrimaryButton :disabled="form.processing">LOGIN</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

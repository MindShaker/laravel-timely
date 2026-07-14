<script setup>
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    user: Object,
    mustVerifyEmail: Boolean,
    status: String,
});

const { props: pageProps } = usePage();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    inicio_almoco: props.user.inicio_almoco ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({ password: '' });
const showDeleteModal = ref(false);
const profileSaved = ref(false);
const passwordSaved = ref(false);

watch(() => pageProps.flash?.status, (val) => {
    if (val === 'profile-updated') {
        profileSaved.value = true;
        setTimeout(() => profileSaved.value = false, 2000);
    }
    if (val === 'password-updated') {
        passwordSaved.value = true;
        setTimeout(() => passwordSaved.value = false, 2000);
    }
}, { immediate: true });

function updateProfile() {
    profileForm.patch(route('profile.update'));
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        errorBag: 'updatePassword',
        onSuccess: () => passwordForm.reset(),
    });
}

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'), {
        errorBag: 'userDeletion',
        onSuccess: () => { showDeleteModal.value = false; },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Perfil" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-white">Profile Information</h2>
                                <Link :href="route('logout')" method="post" as="button"
                                    class="inline-flex items-center px-4 py-2 rounded-md border border-danger-btn-border bg-danger-btn-bg text-xs font-semibold uppercase tracking-widest text-danger-btn-fg hover:bg-danger-btn-hover transition">
                                    LOG OUT
                                </Link>
                            </header>

                            <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="name" value="Name" />
                                    <TextInput id="name" v-model="profileForm.name" type="text" class="mt-1 block w-full p-3" required autofocus autocomplete="name" />
                                    <InputError :message="profileForm.errors.name" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput id="email" v-model="profileForm.email" type="email" class="mt-1 block w-full p-3" required autocomplete="username" />
                                    <InputError :message="profileForm.errors.email" class="mt-2" />
                                    <div v-if="mustVerifyEmail" class="mt-2 text-sm text-content-muted">
                                        Your email address is unverified.
                                        <Link :href="route('verification.send')" method="post" as="button" class="underline hover:text-content">
                                            Re-send verification email.
                                        </Link>
                                        <p v-if="status === 'verification-link-sent'" class="mt-2 text-green-400 text-sm font-medium">
                                            A new verification link has been sent to your email address.
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="inicio_almoco" value="Lunch" />
                                    <TextInput id="inicio_almoco" v-model="profileForm.inicio_almoco" type="time" class="mt-1 block w-full p-3" required />
                                    <InputError :message="profileForm.errors.inicio_almoco" class="mt-2" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <SecondaryButton :disabled="profileForm.processing">SAVE</SecondaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p v-if="profileSaved" class="text-sm text-content-muted">Saved.</p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-white">Update Password</h2>
                                <p class="mt-1 text-sm text-white">Ensure your account is using a long, random password to stay secure.</p>
                            </header>

                            <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="current_password" value="Current Password" />
                                    <TextInput id="current_password" v-model="passwordForm.current_password" type="password" class="mt-1 block w-full p-3" autocomplete="current-password" />
                                    <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="new_password" value="New Password" />
                                    <TextInput id="new_password" v-model="passwordForm.password" type="password" class="mt-1 block w-full p-3" autocomplete="new-password" />
                                    <InputError :message="passwordForm.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Confirm Password" />
                                    <TextInput id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" class="mt-1 block w-full p-3" autocomplete="new-password" />
                                    <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <SecondaryButton :disabled="passwordForm.processing">SAVE</SecondaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p v-if="passwordSaved" class="text-sm text-white">Saved.</p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section class="space-y-6">
                            <header>
                                <h2 class="text-lg font-medium text-white">Delete Account</h2>
                                <p class="mt-1 text-sm text-white">
                                    Once your account is deleted, all of its resources and data will be permanently deleted.
                                    Before deleting your account, please download any data or information that you wish to retain.
                                </p>
                            </header>

                            <button @click="showDeleteModal = true"
                                class="inline-flex items-center px-3 py-2 text-sm leading-5 text-red-400 transition duration-150 ease-in-out hover:bg-chrome-hover border border-neutral-700 rounded disabled:opacity-25">
                                DELETE ACCOUNT
                            </button>

                            <Modal :show="showDeleteModal" max-width="lg" @close="showDeleteModal = false">
                                <div class="p-6">
                                    <h2 class="text-lg font-medium text-white">Are you sure you want to delete your account?</h2>
                                    <p class="mt-1 text-sm text-white">
                                        Once your account is deleted, all of its resources and data will be permanently deleted.
                                        Please enter your password to confirm you would like to permanently delete your account.
                                    </p>
                                    <div class="mt-6">
                                        <InputLabel for="delete_password" value="Password" class="sr-only" />
                                        <TextInput id="delete_password" v-model="deleteForm.password" type="password" class="mt-1 block w-3/4" placeholder="Password" @keyup.enter="deleteAccount" />
                                        <InputError :message="deleteForm.errors.password" class="mt-2" />
                                    </div>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                                        <DangerButton @click="deleteAccount" :disabled="deleteForm.processing">Delete Account</DangerButton>
                                    </div>
                                </div>
                            </Modal>
                        </section>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

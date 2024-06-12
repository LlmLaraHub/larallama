<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import SimpleModal from '@/Components/SimpleModal.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import { TransitionRoot } from '@headlessui/vue'
import ApplicationMark from "@/Components/ApplicationMark.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const usePassword = ref(false);

const showMagicSentModal = ref(false);


const form = useForm({
    email: '',
    password: '',
    remember: false
});

const useMagic = () => {
    usePassword.value = false;
}

const closeMagicModal = () => {
    showMagicSentModal.value = false;
}

const magic = () => {
    showMagicSentModal.value = true;
    axios.post(route('signed_url.create'), {
        email: form.email
    });
}

const passwordInstead = () => {
    usePassword.value = true;
}

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthenticationCard>
        <template #logo>
            <img src="/images/logo.png" class="h-14" />
        </template>

        <div>
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email" type="email" class="w-full  input input-secondary" v-model="form.email" required autofocus autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div class="mt-4" v-if="usePassword">
                    <InputLabel for="password" value="Password" />
                    <TextInput id="password" type="password"
                               class="w-full input input-secondary" v-model="form.password" required autocomplete="current-password" />
                    <InputError class="mt-2" :message="form.errors.password" />


                    <button class="block w-full justify-center flex text-lg
                    mt-4
                    bg-black py-4 rounded-lg font-bold text-2xl" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </button>
                </div>
                <div class="mt-4" v-else>
                    <button
                        :disabled="!form.email"
                        type="button"
                        @click="magic"
                        class="
                            disabled:opacity-70
                            disabled:cursor-not-allowed
                            block w-full justify-center
                    bg-black py-4 rounded-lg font-bold text-2xl">
                        <div>Sign in With Email </div>
                        <div class="text-sm ">(no password needed!)</div>
                    </button>
                    <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-lg mt-2 flex items-start ">

                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                            </svg>

                        </div>
                        <div>
                            We’ll email you a magic code for a password-free sign in. Or you can
                            <button type="button" class="underline" @click="passwordInstead">sign in with password</button>
                            instead.
                        </div>
                    </div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox name="remember"
                                  class="checkbox checked-secondary"
                                  v-model:checked="form.remember" />
                        <span class="ml-2 text-sm">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-center mt-4">
                    <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
                        Forgot password?
                    </Link>
                    <span class="ml-1">|</span>
                    <button v-if="usePassword"
                            type="button"
                            @click="useMagic"
                            class="ml-1 underline text-sm hover:text-gray-900">
                        SignIn with Magic Email?
                    </button>
                    <button v-else
                            type="button"
                            @click="passwordInstead"
                            class="ml-1 underline text-sm hover:text-gray-900">
                        Use password to login
                    </button>
                </div>
            </form>

            <SimpleModal
                @closedModal="closeMagicModal"
                :show-modal="showMagicSentModal">
                <div class="text-lg  font-bold flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8
                     mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" />
                    </svg>
                    Email sent with your password free link to login!
                </div>
            </SimpleModal>

        </div>
    </AuthenticationCard>
</template>



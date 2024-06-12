<script setup>
import {onMounted, ref, computed} from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();

const show = ref(false);

const steps = computed(() => {
    return usePage().props.steps;
})

onMounted(() => {
    if(steps.setup_secrets === false) {
        show.value = true;
    }
})

</script>

<template>
    <div>
        <div v-if="!steps.setup_secrets" class="bg-pink-700">
            <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="w-0 flex-1 flex items-center min-w-0">
                        <span class="flex p-2 rounded-lg bg-pink-600" >

                            <svg  class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </span>

                        <p class="ms-3 font-medium text-sm text-white truncate">
                            Hello! You need to setup a token for any of the available services. Visit
                            <Link :href="route('settings.show')" class="link">Settings</Link> to get started.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

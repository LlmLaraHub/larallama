<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import { useDropzone } from "vue3-dropzone";
import { router, useForm } from '@inertiajs/vue3';
import FileUploader from './Components/FileUploader.vue';

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    chat: {
        type: Object,
    },
});




</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat with {{ collection.data.name }}
            </h2>
        </template>

        <div class="py-12">


            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">


                    <!-- Top area -->

                    <div class="border-b pb-5 px-3 py-4">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">{{ collection.data.name }}</h3>
                        <p class="mt-2 max-w-4xl text-sm text-gray-500">
                            {{ collection.data.description }}
                        </p>
                    </div>
                    <div class="p-10">
                        Chat widget here
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

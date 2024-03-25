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
    documents: {
        type: Object,
    },
});




</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Collection
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
                    <FileUploader :collection="collection" />



                    <!-- show related files -->
                    <div class="px-5">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Related Documents</h1>
                        <p class="mt-2 text-sm text-gray-700">Thsee are a list of documents you uploaded or imported into this Collection and the status of their processing</p>
                    </div>

                    <div>
                        <div class="px-4 sm:px-6 lg:px-8">
                            <div class="mt-8 flow-root">
                                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <div v-if="documents.data.length === 0"
                                            class="text-center text-sm font-medium text-gray-900 px-10 py-10">
                                            No Documents uploaded yet please upload some documents to get started.
                                        </div>
                                        <table class="min-w-full divide-y divide-gray-300" v-else>
                                            <thead>
                                                <tr>
                                                    <th scope="col"
                                                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                        ID
                                                    </th>
                                                    <th scope="col"
                                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                                        Name</th>
                                                    <th scope="col"
                                                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                        Status
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                <tr v-for="document in documents.data" :key="document.id"
                                                    class="even:bg-gray-50">
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        {{ document.id }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        {{ document.file_path }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        {{ document.status }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

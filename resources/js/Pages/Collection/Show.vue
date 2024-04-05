<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import EditCollection from './Edit.vue';
import { useDropzone } from "vue3-dropzone";
import { router, useForm, Link } from '@inertiajs/vue3';
import CollectionTags from './Components/CollectionTags.vue';
import FileUploader from './Components/FileUploader.vue';
import Label from '@/Components/Labels.vue';
import CreateChat from './Components/CreateChat.vue';
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    documents: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});

const showEditCollection = ref(false);

const showEditCollectionSlideOut = () => {
    showEditCollection.value = true;
};
const closeEditCollectionSlideOut = () => {
    showEditCollection.value = false;
};

onMounted(() => {
    Echo.private(`collection.${props.collection.data.id}`)
    .listen('.status', (e) => {
        console.log(e.status);
        router.reload()
    });
});

</script>

<template>
    <AppLayout title="Collection">
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
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">{{ collection.data.name }}</h3>
                            <div class="flex justify-end gap-2 items-center">
                                <CreateChat 
                            v-if="!chat?.data?.id"
                            :collection="collection.data" />
                            <div v-else>
                                <SecondaryLink 
                                class="flex justify-between items-center gap-4"
                                :href="route('chats.collection.show', {
                                    collection: collection.data.id,
                                    chat: chat.data.id
                                })">
                                <ChatBubbleLeftIcon class="h-5 w-5"></ChatBubbleLeftIcon>
                                Continue Chatting</SecondaryLink>

                            </div>
                            <PrimaryButton type="button" @click="showEditCollectionSlideOut">Edit</PrimaryButton>
                            </div>

                        </div>
                        <p class="mt-2 max-w-4xl text-sm text-gray-500">
                            {{ collection.data.description }}
                        </p>
                        <CollectionTags :collection="collection"></CollectionTags>
                    </div>
                    <FileUploader :collection="collection" />



                    <!-- show related files -->
                    <div class="px-5">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Related Documents</h1>
                        <p class="mt-2 text-sm text-gray-700">Thsee are a list of documents you uploaded or imported into this Collection and the status of their processing</p>
                    </div>

                    <div v-auto-animate>
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
                                                        Type</th>
                                                    <th scope="col"
                                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                                        Name</th>
                                                    <th scope="col"
                                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                                        Pages</th>                                                        
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
                                                        {{ document.type }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        {{ document.file_path }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        {{ document.document_chunks_count }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                                        <span v-if="document.status !== 'Pending'" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                            {{ document.status }}
                                                        </span>
                                                        <span v-else class="flex justify-left pl-6">
                                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                           </svg>
                                                        </span>
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
        <EditCollection
        :collection="collection.data"
        :open="showEditCollection" @closing="closeEditCollectionSlideOut"/>
    </AppLayout>
</template>

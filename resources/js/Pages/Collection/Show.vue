<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import EditCollection from './Edit.vue';
import ShowDocument from './Components/ShowDocument.vue';
import { useDropzone } from "vue3-dropzone";
import { router, useForm, Link } from '@inertiajs/vue3';
import CollectionTags from './Components/CollectionTags.vue';
import FileUploader from './Components/FileUploader.vue';
import Label from '@/Components/Labels.vue';
import CreateChat from './Components/CreateChat.vue';
import DocumentReset from './Components/DocumentReset.vue';
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';
import { EllipsisVerticalIcon } from '@heroicons/vue/24/solid';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Tags from '@/Components/Tags.vue';
import ReindexAllDocuments from './Components/ReindexAllDocuments.vue';
import TextDocumentCreate from './Components/TextDocumentCreate.vue';

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

const showReindexCollection = ref(false);

const showSlideOut = ref(null);

const toggleShowSlideOut = (slideOut) => {
    showSlideOut.value = slideOut;
};

const closeSlideOut = () => {
    showSlideOut.value = null;
};

const sourceView = ref('file_upload');

const document = ref({})
const showDocumentSlideOut = ref(false)

const showDocumentButton = (documentToShow) => {
    console.log(documentToShow);
    document.value = documentToShow;
    showDocumentSlideOut.value = true;
};

const closeDocument = () => {
    document.value = {};
    showDocumentSlideOut.value = false;
};

const changeSourceView = (view) => {
    sourceView.value = view;
};

const toggleReindexCollection = () => {
    showReindexCollection.value = !showReindexCollection.value;
};

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
            router.reload({ only: ['documents'] })
        });
});

const reset = () => {
    //router.reload();
}


</script>

<template>
    <AppLayout title="Collection">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Collection
            </h2>
        </template>

        <div class="py-12" v-auto-animate>


            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">


                    <!-- Top area -->

                    <div class="border-b pb-5 px-3 py-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">{{ collection.data.name }}</h3>
                            <div class="flex justify-end gap-2 items-center">
                                <CreateChat v-if="!chat?.data?.id" :collection="collection.data" />
                                <div v-else>
                                    <SecondaryLink class="flex justify-between items-center gap-4" :href="route('chats.collection.show', {
                                collection: collection.data.id,
                                chat: chat.data.id
                            })">
                                        <ChatBubbleLeftIcon class="h-5 w-5"></ChatBubbleLeftIcon>
                                        Continue Chatting
                                    </SecondaryLink>

                                </div>

                                <details class="dropdown dropdown-end">
                                    <summary class="m-1 btn border-none">
                                        <EllipsisVerticalIcon class="h-5 w-5" />
                                    </summary>
                                    <ul class="p-2 shadow menu dropdown-content z-[49] w-52">
                                        <li>
                                            <button type="button" class="btn-link"
                                                @click="showEditCollectionSlideOut">Edit</button>
                                        </li>
                                        <li>
                                            <button type="button" class="btn-link"
                                                @click="toggleReindexCollection">Reindex
                                                Documents</button>
                                        </li>
                                    </ul>
                                </details>
                            </div>

                        </div>
                        <p class="mt-2 max-w-4xl text-sm text-gray-500">
                            {{ collection.data.description }}
                        </p>
                        <CollectionTags :collection="collection"></CollectionTags>
                    </div>
                    <div class="mx-auto max-7xl flex justify-center">
                        <div role="tablist" class="tabs tabs-bordered gap-4">
                            <button @click="changeSourceView('file_upload')" type="button" role="tab" class="tab"
                                :class="{ 'tab-active text-indigo-700 font-bold border-indigo-700': sourceView === 'file_upload' }">Upload
                                Files</button>
                            <button @click="changeSourceView('text')" type="button" role="tab" class="tab"
                                :class="{ 'tab-active text-indigo-700 font-bold border-indigo-700': sourceView === 'text' }">
                                Other Integrations
                            </button>
                        </div>
                    </div>

                    <FileUploader :collection="collection" v-show="sourceView === 'file_upload'" />

                    <div v-show="sourceView === 'text'" class="grid grid-cols-3 max-w-4xl mt-10 mb-10 mx-auto">
                        <button @click="toggleShowSlideOut('textDocument')" type="button"
                            class="px-5 py-5 border border-gray-300 rounded shadow-md flex-col text-center mx-auto justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-10 h-10 text-gray-400 mx-auto">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <div class="text-gray-500 text-md font-semibold">
                                Add Document using Text Editor
                            </div>
                        </button>
                    </div>

                    <!-- show related files -->
                    <div class="px-5">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Related Documents</h1>
                        <p class="mt-2 text-sm text-gray-700">Thsee are a list of documents you uploaded or imported
                            into this
                            Collection and the status of their processing</p>
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
                                        <table class="min-w-full divide-y divide-gray-300 mb-10" v-else>
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
                                                    <th scope="col"
                                                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                <template v-for="document in documents.data" :key="document.id">
                                                    <tr class="even:bg-gray-50">
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
                                                            <span v-if="document.status !== 'Pending'"
                                                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                                {{ document.status }}
                                                            </span>
                                                            <span v-else class="flex justify-left pl-6">
                                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                        stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor"
                                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                    </path>
                                                                </svg>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <ul>
                                                                <li>
                                                                    <button type="button" class="text-gray-500 text-sm flex justify-start gap-2 items-center flex justify-start gap-2 items-center"
                                                                        @click="showDocumentButton(document)">
                                                                        <span>view</span>

                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                        </svg>
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <DocumentReset :collection="collection.data"
                                                                        :document="document" @reset="reset" />
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                    <tr class="justify-center gap-2 items-center">
                                                        <td colspan="6" class="w-full">
                                                            <Tags :document="document"></Tags>
                                                        </td>
                                                    </tr>
                                                </template>
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
        <EditCollection :collection="collection.data" :open="showEditCollection"
            @closing="closeEditCollectionSlideOut" />
        <ShowDocument :document="document" :open="showDocumentSlideOut"
            @closing="closeDocument" />            
        <TextDocumentCreate :collection="collection.data" :open="showSlideOut === 'textDocument'"
            @closing="closeSlideOut" />
        <ConfirmationModal :show="showReindexCollection" @close="toggleReindexCollection">
            <template #title>
                Reindex Collection
            </template>
            <template #content>
                This will remove all existing indexes and start the process over
                with your current set of uploaded documents. Are you sure?
                This will NOT delete chats threads.
            </template>
            <template #footer>
                <div class="flex justify-end gap-4 items-center">
                    <SecondaryButton @click="toggleReindexCollection">
                        Cancel
                    </SecondaryButton>

                    <ReindexAllDocuments :collection="collection.data" @reindexed="toggleReindexCollection" />
                </div>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

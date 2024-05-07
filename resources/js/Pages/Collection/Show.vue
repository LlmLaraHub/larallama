<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import CollectionHeader from './Components/CollectionHeader.vue';
import Documents from '@/Pages/Collection/Components/Documents.vue';

import EditCollection from './Edit.vue';
import ShowDocument from './Components/ShowDocument.vue';
import Nav from './Components/Nav.vue';
import { useDropzone } from "vue3-dropzone";
import { router, useForm, Link } from '@inertiajs/vue3';
import FileUploader from './Components/FileUploader.vue';
import DocumentReset from './Components/DocumentReset.vue';
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

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-12" v-auto-animate>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <CollectionHeader 
                    :chat="chat?.data"
                    :collection="collection.data"></CollectionHeader>

                    <!-- Files upload -->

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
                    <Documents :collection="collection.data" :documents="documents.data"></Documents>
                </div>
            </div>
        </div>
        <EditCollection :collection="collection.data" :open="showEditCollection"
            @closing="closeEditCollectionSlideOut" />

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

        <TextDocumentCreate :collection="collection.data" :open="showSlideOut === 'textDocument'"
            @closing="closeSlideOut" />
    </AppLayout>
</template>

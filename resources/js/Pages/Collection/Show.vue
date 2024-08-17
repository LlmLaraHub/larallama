<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import CollectionHeader from '@/Pages/Collection/Components/CollectionHeader.vue';
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
import { useToast } from 'vue-toastification';
import CollectionNav from "@/Pages/Collection/Components/CollectionNav.vue";

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    documents: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});

const toast = useToast();

const showReindexCollection = ref(false);


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



const reset = () => {
    //router.reload();
}


</script>

<template>
    <AppLayout title="Collection">
        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-12" v-auto-animate>
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <CollectionHeader
                    @toggleReindexCollection="toggleReindexCollection"
                    @showEditCollectionSlideOut="showEditCollectionSlideOut"
                    :chat="chat?.data"
                    :collection="collection.data"></CollectionHeader>



                    <CollectionNav :collection="collection.data"></CollectionNav>

                    <!-- show related files -->
                    <Documents
                        :filters="filters.data"
                        :collection="collection.data" :documents="documents"></Documents>
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


    </AppLayout>
</template>

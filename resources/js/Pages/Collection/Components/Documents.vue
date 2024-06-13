<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue';
import Tags from '@/Components/Tags.vue';
import ShowDocument from '@/Pages/Collection/Components/ShowDocument.vue';
import DocumentReset from '@/Pages/Collection/Components/DocumentReset.vue';
import ActionDeleteDocuments from "@/Pages/Collection/Components/ActionDeleteDocuments.vue";
import ActionCreateFilter from "@/Pages/Collection/Components/ActionCreateFilter.vue";
import Filters from "@/Pages/Collection/Components/Filters.vue";
import ManageFilters from "@/Pages/Collection/Components/ManageFilters.vue";
import {router} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";

const toast = useToast();

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
    }
});

const document = ref({})
const showDocumentSlideOut = ref(false)


const showDocumentButton = (documentToShow) => {
    document.value = documentToShow;
    showDocumentSlideOut.value = true;
};

const closeDocument = () => {
    document.value = {};
    showDocumentSlideOut.value = false;
};

const selectedDocuments = ref(new Set)

const selectedDocumentsToArray = computed(() => {
    return Array.from(selectedDocuments.value);
})

const checked = (item) => {
    if (selectedDocuments.value.has(item)) {
        selectedDocuments.value.delete(item);
    } else {
        selectedDocuments.value.add(item);
    }
}

const emptyDocumentIds = () => {
    console.log("Resetting documents");
    selectedDocuments.value = new Set()
}
onMounted(() => {
    Echo.private(`collection.${props.collection.id}`)
        .listen('.status', (e) => {
            console.log(e.status);
            router.reload({ only: ['documents'] })
            let message = e.message;
            if (message) {
                if(message !== 'Processing Document') {
                    toast.info(message)
                }
            }
        });
});

onUnmounted(() => {
    Echo.leave(`collection.${props.collection.id}`);
});

</script>
<template>
    <div class="px-5">

        <div class="flex justify-end mx-10 ">
            <ManageFilters :collection="collection" :filters="filters"></ManageFilters>
        </div>
        <h1 class="text-base font-semibold leading-6">Related Documents</h1>
        <p class="mt-2 text-sm ">These are a list of documents you uploaded or imported
            into this
            Collection and the status of their processing</p>
    </div>

    <div v-auto-animate>
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                       <Transition
                           enter-from-class="opacity-0"
                           enter-to-class="opacity-100 scale-100"
                           enter-active-class="transition duration-300"
                           leave-active-class="transition duration-200"
                           leave-from-class="opacity-100 scale-100"
                           leave-to-class="opacity-0"
                       >
                           <div v-if="selectedDocumentsToArray.length > 0"
                           >
                               <div  class="flex justify-start gap-2 items-center">
                                   <div class="font-bold text-secondary">Actions:</div>
                                   <ActionDeleteDocuments
                                       @deleted="emptyDocumentIds"
                                       :document-ids="selectedDocumentsToArray"></ActionDeleteDocuments>

                                   <ActionCreateFilter
                                       @created="emptyDocumentIds"
                                       :document-ids="selectedDocumentsToArray" :collection="collection"></ActionCreateFilter>
                               </div>
                           </div>
                       </Transition>
                        <div v-if="documents.length === 0"
                            class="text-center text-sm font-medium px-10 py-10">
                            No Documents uploaded yet please upload some documents to get started.
                        </div>
                        <div class="overflow-x-auto" v-else>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>

                                        </th>
                                        <th>
                                            ID
                                        </th>

                                        <th>
                                            Type</th>
                                        <th>
                                            Name</th>
                                        <th>
                                            Children</th>
                                        <th>
                                            Parent Id</th>
                                        <th>
                                            Pages</th>
                                        <th>
                                            Tags</th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="document in documents" :key="document.id">
                                        <tr class="even:bg-base-200">
                                            <td>
                                                <input type="checkbox"  @change="checked(document.id)"/>
                                            </td>
                                            <td>
                                                {{ document.id }}
                                            </td>
                                            <td>
                                                {{ document.type }}
                                            </td>
                                            <td>
                                                    <div v-if="document.type === 'Json'" class="truncate w-80">
                                                        {{ document.subject }}
                                                    </div>
                                                    <div v-else-if="document.subject">
                                                        <div class="truncate max-w-2xl">
                                                            <div v-if="!document.link">
                                                                {{ document.subject }}
                                                            </div>
                                                            <div v-else>
                                                                <a class="underline" target="_blank" :href="document.link">
                                                                    {{ document.subject }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="text-gray-400 text-sm">
                                                            <a class="underline" target="_blank" :href="route('download.document', {
                                                                    collection: collection.id,
                                                                    document_name: document.file_path
                                                                })">
                                                                {{ document.file_path }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div v-else>
                                                        <a class="underline" target="_blank" :href="route('download.document', {
                                                            collection: collection.id,
                                                            document_name: document.file_path
                                                        })">
                                                            {{ document.file_path }}
                                                        </a>
                                                    </div>
                                                <div class="text-xs text-secondary">updated: {{ document.updated_at_diff }}</div>
                                            </td>

                                            <td>
                                                {{ document.children_count }}
                                            </td>

                                            <td>
                                                {{ document.parent_id }}
                                            </td>
                                            <td>
                                                {{ document.document_chunks_count }}
                                            </td>
                                            <td>
                                                {{ document.tags_count }}
                                            </td>
                                            <td>
                                                <div v-if="document.status !== 'Pending'" class="badge badge-secondary">{{ document.status }}</div>
                                                <span v-else class="loading loading-infinity loading-sm"></span>
                                            </td>
                                            <td>
                                                <ul>
                                                    <li>
                                                        <button type="button"
                                                            class="text-gray-500 text-sm flex justify-start gap-2 items-center flex justify-start gap-2 items-center"
                                                            @click="showDocumentButton(document)">
                                                            <span>view</span>

                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="w-4 h-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                            </svg>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <DocumentReset :collection="collection" :document="document"
                                                            @reset="reset" />
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr v-if="false" class="justify-center gap-2 items-center">
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
    <Teleport to="body">

    <ShowDocument :document="document" :open="showDocumentSlideOut"
            @closing="closeDocument" />
    </Teleport>

</template>

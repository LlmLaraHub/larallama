<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue';
import Tags from '@/Components/Tags.vue';
import ShowDocument from '@/Pages/Collection/Components/ShowDocument.vue';
import DocumentReset from '@/Pages/Collection/Components/DocumentReset.vue';
import ActionDeleteDocuments from "@/Pages/Collection/Components/ActionDeleteDocuments.vue";
import ActionCreateFilter from "@/Pages/Collection/Components/ActionCreateFilter.vue";
import Filters from "@/Pages/Collection/Components/Filters.vue";
import ManageFilters from "@/Pages/Collection/Components/ManageFilters.vue";
import {router, useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import Pagination from "@/Pages/Collection/Components/Pagination.vue";
import DocumentStatus from "@/Pages/Collection/Components/DocumentStatus.vue";

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

const documentsList = ref([])

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
    router.reload({ only: ['documents', 'collection'] })
}

onMounted(() => {
    getDocuments();
    Echo.private(`collection.${props.collection.id}`)
        .listen('.status', (e) => {
            router.reload({ only: ['documents'] })
        });
});

onUnmounted(() => {
    Echo.leave(`collection.${props.collection.id}`);
});


const form = useForm({
    filter: ""
})

const getDocuments = (filterBy) => {
    //for now
    // instead of fixing this I will make to Inertia Tables

    documentsList.value = props.documents;
    return;
    form.filter = filterBy;
    form.processing = true;
    axios.get(route('collections.documents.index', {
        collection: props.collection.id,
        filter: form.filter
    })).then(response => {
        console.log(response.data);
        documentsList.value = response.data.documents;
        form.processing = false;
    }).catch(error => {
        console.log(error)
        form.processing = false;
    })
}

const isChecked = (item) => {
    return [...selectedDocuments.value].some(existingItem => existingItem === item);
}

const toggledAll = ref(false);

const toggleAll = () => {
    toggledAll.value = !toggledAll.value;


    if ([...props.documents.data].every(item => selectedDocuments.value.has(item.id))) {
        for (const item of props.documents.data) {
            selectedDocuments.value.delete(item.id);
        }
    } else {
        for (const item of props.documents.data) {
            if (!selectedDocuments.value.has(item.id)) {
                selectedDocuments.value.add(item.id);
            }
        }
    }
}

const featureShowFilters = ref(false)

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
        <div class="justify-center flex w-full mx-auto mt-4 gap-3 items-center">
            <div class="flex gap-2 items-center justify-start">
                <button type="button" @click="getDocuments('pending')"   v-if="featureShowFilters"
                        class="btn btn-default rounded-none">
                    Show Not Completed
                    <span
                        v-show="form.processing && form.filter === 'pending'"
                        class="loading loading-spinner loading-xs"></span>
                </button>
                <button type="button" @click="getDocuments('complete')"   v-if="featureShowFilters"
                        class="btn btn-default rounded-none">
                    Show Completed
                    <span
                        v-show="form.processing && form.filter === 'complete'"
                        class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
            <div class="justify-center flex">
                <DocumentStatus :collection="collection" ></DocumentStatus>
            </div>
        </div>
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
                                       :collection="collection"
                                       @deleted="emptyDocumentIds"
                                       @deletedAll="emptyDocumentIds"
                                       :document-ids="selectedDocumentsToArray"></ActionDeleteDocuments>

                                   <ActionCreateFilter
                                       @created="emptyDocumentIds"
                                       :document-ids="selectedDocumentsToArray" :collection="collection"></ActionCreateFilter>
                               </div>
                           </div>
                       </Transition>

                        <div v-if="documents.data.length === 0"
                            class="text-center text-sm font-medium px-10 py-10">
                            No Documents uploaded yet please upload some documents to get started.
                        </div>
                        <div class="overflow-x-auto" v-else>
                            <div class="form-control w-32">
                                <label class="label cursor-pointer">
                                    <span v-if="toggledAll" class="label-text">
                                        Un-Check All
                                    </span>
                                    <span v-else class="label-text">
                                        Check All
                                    </span>
                                    <input
                                        :checked="toggledAll"
                                        @change="toggleAll()"
                                        type="checkbox" checked="checked" class="checkbox" />
                                </label>
                            </div>
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
                                        <th v-if="false">
                                            Children</th>
                                        <th v-if="false">
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
                                    <template v-for="document in documents.data" :key="document.id">
                                        <tr class="even:bg-base-200">
                                            <td>
                                                <input type="checkbox"

                                                       :checked="isChecked(document.id)"
                                                       @change="checked(document.id)"
                                                />
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
                                                            <a
                                                                :href="document.file_path"
                                                                v-if="document.type === 'Html'"
                                                                class="underline truncate w-8" target="_blank">
                                                                {{ document.file_path }}
                                                            </a>
                                                            <a v-else class="underline" target="_blank" :href="route('download.document', {
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
                                                <div class="text-xs text-secondary flex w-full items-center justify-start">
                                                    <div>updated: {{ document.updated_at_diff }}</div>
                                                    <div class="text-secondary mx-2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-2">
                                                        <path fill-rule="evenodd" d="M9.58 1.077a.75.75 0 0 1 .405.82L9.165 6h4.085a.75.75 0 0 1 .567 1.241l-6.5 7.5a.75.75 0 0 1-1.302-.638L6.835 10H2.75a.75.75 0 0 1-.567-1.241l6.5-7.5a.75.75 0 0 1 .897-.182Z" clip-rule="evenodd" />
                                                    </svg>
                                                    </div>
                                                    <div>created at: {{ document.created_at_diff }}</div>
                                                </div>
                                            </td>

                                            <td v-if="false">
                                                {{ document.children_count }}
                                            </td>

                                            <td v-if="false">
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
                        <div>
                            <Pagination
                                :meta="documents" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Teleport to="body">

    <ShowDocument
        v-if="showDocumentSlideOut"
        :document="document" :open="showDocumentSlideOut"
            @closing="closeDocument" />
    </Teleport>

</template>

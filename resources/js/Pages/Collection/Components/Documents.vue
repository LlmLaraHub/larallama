<script setup>
import {computed, ref} from 'vue';
import Tags from '@/Components/Tags.vue';
import ShowDocument from '@/Pages/Collection/Components/ShowDocument.vue';
import DocumentReset from '@/Pages/Collection/Components/DocumentReset.vue';
import ActionDeleteDocuments from "@/Pages/Collection/Components/ActionDeleteDocuments.vue";
import ActionCreateFilter from "@/Pages/Collection/Components/ActionCreateFilter.vue";
import Filters from "@/Pages/Collection/Components/Filters.vue";

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



</script>
<template>
    <div class="px-5">

        <div class="flex justify-end mx-10 ">
            <Filters :collection="collection" :filters="filters"></Filters>
        </div>
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
                                   <div class="text-gray-600 font-bold ">Actions:</div>
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
                            class="text-center text-sm font-medium text-gray-900 px-10 py-10">
                            No Documents uploaded yet please upload some documents to get started.
                        </div>
                        <table class="min-w-full divide-y divide-gray-300 mb-10 " v-else>
                            <thead>
                                <tr>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">

                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
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
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                        Tags</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Status
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <template v-for="document in documents" :key="document.id">
                                    <tr class="even:bg-gray-50">
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                            <input type="checkbox"  @change="checked(document.id)"/>
                                        </td>
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

                                            <a class="underline" target="_blank" :href="route('download.document', {
                            collection: collection.id,
                            document_name: document.file_path
                        })">{{ document.file_path }}</a>

                                        </td>
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                            {{ document.document_chunks_count }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                            {{ document.tags_count }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                            <span v-if="document.status !== 'Pending'"
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                {{ document.status }}
                                            </span>
                                            <span v-else class="flex justify-left pl-6">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
    <Teleport to="body">

    <ShowDocument :document="document" :open="showDocumentSlideOut"
            @closing="closeDocument" />
    </Teleport>

</template>

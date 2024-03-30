<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CreateCollection from './Create.vue';
import EditCollection from './Edit.vue';
import { ref } from 'vue';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';


const props = defineProps({
    collections: {
        type: Object,
        required: true,
    },
});

const showCreateCollection = ref(false);

const showEditCollection = ref(false);

const collectionToEdit = ref({});

const closeCreateCollectionSlideOut = () => {
    showCreateCollection.value = false;
};

const showEditCollectionSlideOut = (collection) => {
    collectionToEdit.value = collection;
    showEditCollection.value = true;
};
const closeEditCollectionSlideOut = () => {
    collectionToEdit.value = {};
    showEditCollection.value = false;
};


</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex justify-end mt-2 mr-2 -mb-10">
                        <SecondaryButton @click="showCreateCollection = true" v-if="collections.data.length !== 0">
                            Add another Collection
                        </SecondaryButton>
                    </div>
                    <Welcome />


                    <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 p-6">
                        <div>
                            <div class="mt-2 flow-root">
                                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <div v-if="collections.data.length === 0">
                                            <div>
                                                <PrimaryButton @click="showCreateCollection = true">
                                                    Start by creating a new collection.
                                                </PrimaryButton>
                                            </div>
                                        </div>
                                        <div 

                                        class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg p-4 bg-white mb-4"
                                        v-else v-for="collectionItem in collections.data" :key="collectionItem.id">
                                            <div>
                                               <div class="font-bold text-lg text-gray-500">
                                                {{  collectionItem.name }}
                                               </div> 
                                               <div class="text-md text-gray-500 font-semibold">
                                                    {{  collectionItem.description }}
                                               </div>
                                               <div class="flex justify-center mt-10 gap-2">
                                                    <span class="rounded-md bg-indigo-500 text-indigo-200 px-2 py-2 ">
                                                        LLm Driver: {{ collectionItem.driver }}
                                                    </span>
                                                    <span class="rounded-md bg-indigo-500 text-indigo-200 px-2 py-2 ">
                                                        Embedding Driver: {{ collectionItem.driver }}
                                                    </span>
                                                    <span class="rounded-md bg-indigo-500 text-indigo-200 px-2 py-2 ">
                                                        Document Count: {{ collectionItem.documents_count }}
                                                    </span>
                                                    <span class="rounded-md bg-indigo-500 text-indigo-200 px-2 py-2 ">
                                                        Tag:Test
                                                    </span>
                                                    <span class="rounded-md bg-indigo-500 text-indigo-200 px-2 py-2 ">
                                                        Tag:Other Tag
                                                    </span>
                                               </div>
                                            </div>
                                            <div class="flex justify-end gap-2 mt-4">
                                                <PrimaryButtonLink :href="route('collections.show', {
                                                    collection: collectionItem.id
                                                })">view</PrimaryButtonLink>

                                                <SecondaryButton
                                                type="button"
                                                @click="showEditCollectionSlideOut(collectionItem)">
                                                    Edit
                                                </SecondaryButton>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <CreateCollection :open="showCreateCollection" @closing="closeCreateCollectionSlideOut"/>
        <EditCollection v-if="collectionToEdit?.id"
        :collection="collectionToEdit"
        :open="showEditCollection" @closing="closeEditCollectionSlideOut"/>
    </AppLayout>
</template>

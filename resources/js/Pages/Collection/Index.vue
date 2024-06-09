<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CreateCollection from './Create.vue';
import EditCollection from './Edit.vue';
import { ref } from 'vue';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';
import {usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";


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
    <AppLayout title="Your Collections">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex justify-end mt-2 mr-2 sm:-mb-10">
                        <SecondaryButton @click="showCreateCollection = true" v-if="collections.data.length !== 0">
                            Add another Collection
                        </SecondaryButton>
                    </div>

                    <div class="hidden sm:block">
                        <div>
                            <div class="py-12 px-10 border-b border-primary-500">
                                <ApplicationLogo class="block h-12 w-auto" />

                                <h1 class="mt-8 text-2xl font-medium ">
                                    Welcome to {{  usePage().props.app_name }}, where your data transformation journey begins! Dive into a seamless experience of managing and analyzing your data with precision and ease.
                                </h1>

                                <p class="mt-6 text-gray-500 leading-relaxed">
                                    Get started by creating a new 'Collection'—your first step towards organizing diverse data sources like PDFs, website data, and more, into a cohesive unit. Each 'Collection' serves as a centralized hub for your team's data, making it easier to collaborate, analyze, and derive actionable insights. Simply select or switch your team from the menu, and create or access your Collections to embark on a streamlined data management journey. Ready to unlock the full potential of your data? Let's create your first Collection and set the foundation for unparalleled team collaboration and insight discovery.
                                </p>
                            </div>


                        </div>
                    </div>


                    <div class="bg-opacity-25 grid grid-cols-1 p-6">
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
                                        <div v-else class="grid grid-cols-1 gap-x-4 gap-y-10 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-16">
                                            <template
                                                 v-for="collectionItem in collections.data" :key="collectionItem.id">
                                                <div class="card w-96 bg-base-100 shadow-xl">
                                                    <div class="card-body">
                                                        <h2 class="card-title">
                                                            <span class="text-accent text-sm"> #{{ collectionItem.id }}</span> {{  collectionItem.name }}
                                                        </h2>
                                                        <p class="truncate max-h-80">{{  collectionItem.description }}</p>
                                                        <div class="card-actions justify-end">
                                                            <PrimaryButtonLink :href="route('collections.show', {
                                                    collection: collectionItem.id
                                                })">view</PrimaryButtonLink>

                                                            <SecondaryButton
                                                                type="button"
                                                                @click="showEditCollectionSlideOut(collectionItem)">
                                                                Edit
                                                            </SecondaryButton>
                                                        </div>

                                                        <div class="flex justify-center gap-2">
                                                            <div class="badge badge-xs">LLm: {{ collectionItem.driver }}</div>
                                                            <div class="badge badge-xs">Embedding: {{ collectionItem.driver }}</div>
                                                            <div class="badge badge-xs">Documents: {{ collectionItem.documents_count }}</div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </template>
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

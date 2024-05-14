<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';
import Nav from '@/Pages/Collection/Components/Nav.vue';
import Documents from '@/Pages/Collection/Components/Documents.vue';
import Intro from '@/Components/Intro.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    outputs: {
        type: Object
    },
    documents: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});


</script>

<template>
    <AppLayout title="Sources">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sources
            </h2>
        </template>

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2">
                 <Intro>
                    Manage Outputs
                    <template #description>
                        Outputs are ways to access the data. To start you can make a
                        web page that is chattable from a collection or an api.
                    </template>

                 </Intro>

                  <div class="border border-gray-200 p-5 mt-5 flex">
                    <div v-if="outputs.data.length === 0" class="text-center w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                        </svg>
                        <div class="text-xl text-gray-600">No Outputs yet. Choose one below</div>
                    </div>
                    <div v-else class="card rounded-none w-96 bg-base-100 shadow-xl" v-for="output in outputs.data" :key="output.id">
                        <div class="card-body">
                            <h2 class="card-title text-gray-600">{{ output.title }}</h2>

                            <div class="overflow-hidden">
                                <span class="font-bold text-gray-600 text-xs" v-html="output.summary_truncated"></span>
                            </div>

                            <div class="card-actions justify-end flex items-center">
                                <Link class="link" :href="output.url">visit</Link>
                                <Link :href="route('collections.outputs.web_page.edit', {
                                    collection: output.collection_id,
                                    output: output.id
                                })" class="btn btn-primary rounded-none">Edit</Link>
                            </div>
                        </div>
                    </div>

                  </div>

                  <div class="mt-5 mx-10">
                        <h3 class="font-bold text-gray-700">Available Outputs</h3>
                        <div class="flex justify-start items-center gap-2">

                            <Link
                            class="btn btn-gray rounded-none"
                            :href="route('collections.outputs.web_page.create',
                                {collection: collection.data.id}
                            )"
                            >Web Page</Link>

                            <Link
                                class="btn btn-dark rounded-none"
                                :href="route('collections.outputs.web_page.create',
                                {collection: collection.data.id}
                            )"
                            >Api</Link>

                        </div>
                    </div>

                 <div class="mt-10">
                    <Documents :collection="collection.data" :documents="documents.data"></Documents>
                 </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

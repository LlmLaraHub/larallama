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
    sources: {
        type: Object
    },
    documents: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});

const form = useForm({})

const run = (source) => {
    form.post(route('collections.sources.run', {
        source: source.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Source is running');
        }
    });
}
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
                    Manage Sources
                    <template #description>
                        Sources are ways you can add data to your collection beyond uploading documents.
                        You can add via a websearch, and soon email and calendar.
                    </template>

                 </Intro>

                  <div class="border border-gray-200 p-5 mt-5 flex">
                    <div v-if="sources.data.lenth === 0" class="text-gray-500 text-lg">
                        No sources yet.
                    </div>
                    <div v-else class="card rounded-none w-96 bg-base-100 shadow-xl" v-for="source in sources.data" :key="source.id">
                        <div class="card-body">
                            <h2 class="card-title text-gray-600">{{ source.title }}</h2>
                            <div>
                                Type: <span class="font-bold text-gray-600">{{ source.type }}</span>
                            </div>
                            <div class="card-actions justify-end">
                                <button @click="run(source)" type="button" class="btn btn-primary rounded-none">Run</button>
                                <button class="btn btn-primary rounded-none">Edit</button>
                            </div>
                        </div>
                    </div>

                  </div>

                  <div class="mt-5 mx-10">
                        <h3 class="font-bold text-gray-700">Available Sources</h3>
                        <div class="flex justify-start items-center">

                            <Link
                            class="btn btn-info rounded-none"
                            :href="route('collections.sources.websearch.create', 
                                {collection: collection.data.id}
                            )"
                            >Web Search</Link>
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

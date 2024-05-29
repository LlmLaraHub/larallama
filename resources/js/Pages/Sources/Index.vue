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
import Card from "@/Pages/Sources/Cards/Card.vue";
import EmailCard from "@/Pages/Sources/EmailSource/Components/Card.vue";
import EmailBoxCard from "@/Pages/Sources/EmailBoxSource/Components/Card.vue";

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
    filters: {
        type: Object,
    },
    available_sources: Object
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
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2">
                 <Intro>
                    Manage Sources
                    <template #description>
                        Sources are ways you can add data to your collection beyond uploading documents.
                        You can add via a websearch, and soon email and calendar.
                    </template>

                 </Intro>

                  <div class="border border-gray-200 p-5 mt-5 flex">
                    <div v-if="sources.data.length === 0" class="text-center w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                        </svg>
                        <div class="text-xl text-gray-600">No sources yet. Choose one below</div>
                    </div>
                      <template v-else  v-for="source in sources.data" :key="source.id">
                          <EmailCard v-if="source.type_key === 'email_source'" :source="source"></EmailCard>
                          <EmailBoxCard v-else-if="source.type_key === 'email_box_source'" :source="source"></EmailBoxCard>
                          <Card v-else :source="source"></Card>
                      </template>

                  </div>

                  <div class="mt-5 mx-10">
                        <h3 class="font-bold text-gray-700">Available Sources</h3>
                        <div class="flex justify-start items-center gap-2"
                        >
                            <template v-for="available_source in available_sources" :key="name">
                                <Link
                                    class="btn btn-default rounded-none"
                                    :href="available_source.route"
                                >{{ available_source.name }}</Link>
                            </template>
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

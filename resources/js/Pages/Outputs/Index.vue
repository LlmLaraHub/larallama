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
import WebCard from "@/Pages/Outputs/WebPage/Components/Card.vue";
import EmailCard from "@/Pages/Outputs/EmailOutput/Components/Card.vue";
import ApiCard from "@/Pages/Outputs/ApiOutput/Components/Card.vue";

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
    available_outputs: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});


</script>

<template>
    <AppLayout title="Outputs">

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg p-2">
                 <Intro>
                    Manage Outputs
                    <template #description>
                        Outputs are ways to access the data. To start you can make a
                        web page that is chattable from a collection or an api.
                    </template>

                 </Intro>

                  <div class="border border-secondary p-5 mt-5 flex">
                    <div v-if="outputs.data.length === 0" class="text-center w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                        </svg>
                        <div class="text-xl">No Outputs yet. Choose one below</div>
                    </div>
                    <div v-else  class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <template
                            v-for="output in outputs.data" :key="output.id">
                            <EmailCard v-if="output.type === 'email_output'" :output="output"/>
                            <WebCard v-if="output.type === 'web_page'" :output="output"/>
                            <ApiCard v-if="output.type === 'api_output'" :output="output"/>
                        </template>
                    </div>
                  </div>

                  <div class="mt-5 mx-10">
                        <h3 class="font-bold mb-2">Available Outputs</h3>
                        <div class="flex justify-start items-center gap-2">

                            <template v-for="available in available_outputs" :key="available.name">
                                <Link
                                    v-if="available.active"
                                    class="btn btn-secondary rounded-none"
                                    :href="available.route"
                                >{{ available.name}}</Link>
                                <div

                                    class="btn rounded-none"
                                    v-else>{{available.name}} (coming soon..)</div>
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

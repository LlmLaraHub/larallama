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
import WebhookSource from "@/Pages/Sources/WebhookSource/Components/Card.vue";
import FeedSource from "@/Pages/Sources/FeedSource/Components/Card.vue";
import WebPageSource from "@/Pages/Sources/WebPageSource/Components/Card.vue";
import SiteMapSource from "@/Pages/Sources/SiteMapSource/Components/Card.vue";
import GoogleSheetSource from "@/Pages/Sources/GoogleSheetSource/Components/Card.vue";
import FileUploader from "@/Pages/Collection/Components/FileUploader.vue";
import TextDocumentCreate from "@/Pages/Collection/Components/TextDocumentCreate.vue";

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


const sourceView = ref('file_upload');


const changeSourceView = (view) => {
    sourceView.value = view;
};


const showSlideOut = ref(null);

const toggleShowSlideOut = (slideOut) => {
    showSlideOut.value = slideOut;
};

const closeSlideOut = () => {
    showSlideOut.value = null;
};

</script>

<template>
    <AppLayout title="Sources">
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Sources
            </h2>
        </template>

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg p-2">
                 <Intro>
                    Manage Sources
                    <template #description>
                        Sources are ways you can add data to your collection beyond uploading documents.
                        You can add via a websearch, and soon email and calendar.
                    </template>
                 </Intro>
                  <div class="border border-secondary p-5 mt-5 flex">
                    <div v-if="sources.data.length === 0" class="text-center w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                        </svg>
                        <div class="text-xl">No sources yet. Choose one below</div>
                    </div>
                      <div v-else class="grid grid-cols-1 gap-x-4 gap-y-10 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-16">
                          <template  v-for="source in sources.data" :key="source.id">
                              <EmailCard v-if="source.type_key === 'email_source'" :source="source"></EmailCard>
                              <EmailBoxCard v-else-if="source.type_key === 'email_box_source'" :source="source"></EmailBoxCard>
                              <WebhookSource v-else-if="source.type_key === 'webhook_source'" :source="source"></WebhookSource>
                              <FeedSource v-else-if="source.type_key === 'feed_source'" :source="source"></FeedSource>
                              <WebPageSource v-else-if="source.type_key === 'web_page_source'" :source="source"></WebPageSource>
                              <SiteMapSource v-else-if="source.type_key === 'site_map_source'" :source="source"></SiteMapSource>
                              <GoogleSheetSource v-else-if="source.type_key === 'google_sheet_source'" :source="source"></GoogleSheetSource>
                              <Card v-else :source="source"></Card>
                          </template>
                      </div>

                  </div>

                  <div class="mt-5 mx-10">
                        <h3 class="font-bold">Available Sources</h3>
                        <div class="flex justify-center items-center gap-2 flex-wrap"
                        >
                            <template v-for="available_source in available_sources" :key="name">
                                <Link
                                    class="btn btn-secondary rounded-none"
                                    :href="available_source.route"
                                >{{ available_source.name }}</Link>
                            </template>
                        </div>
                    </div>

                 <div>
                     <h2>Upload Files or use our Text Editor to add content</h2>
                     <!-- Files upload -->

                     <div class="mx-auto max-7xl flex justify-center">
                         <div role="tablist" class="tabs tabs-bordered gap-4">
                             <button @click="changeSourceView('file_upload')" type="button" role="tab" class="tab"
                                     :class="{ 'tab-active font-bold': sourceView === 'file_upload' }">Upload
                                 Files</button>
                             <button @click="changeSourceView('text')" type="button" role="tab" class="tab"
                                     :class="{ 'tab-active font-bold': sourceView === 'text' }">
                                 Other Integrations
                             </button>
                         </div>
                     </div>

                     <FileUploader :collection="collection" v-show="sourceView === 'file_upload'" />

                     <div v-show="sourceView === 'text'" class="grid grid-cols-3 max-w-4xl mt-10 mb-10 mx-auto">
                         <div class="card w-96 bg-base-100 shadow-xl">
                             <div class="card-body">
                                 <button class="btn btn-ghost" type="button"
                                         @click="toggleShowSlideOut('textDocument')"
                                 >
                                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                          stroke="currentColor" class="w-10 h-10">
                                         <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                     </svg>
                                     Add Document using Text Editor
                                 </button>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="mt-10">
                    <Documents :collection="collection.data"
                               :documents="documents"></Documents>
                 </div>
                </div>

            </div>

        </div>

        <TextDocumentCreate :collection="collection.data"
                            :open="showSlideOut === 'textDocument'"
                            @closing="closeSlideOut" />
    </AppLayout>
</template>

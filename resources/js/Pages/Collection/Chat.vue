<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import { computed, onMounted, provide, ref } from 'vue';

import CollectionTags from './Components/CollectionTags.vue';
import { useDropzone } from "vue3-dropzone";
import { router, useForm } from '@inertiajs/vue3';
import FileUploader from './Components/FileUploader.vue';
import ChatUi from '@/Pages/Chat/ChatUi.vue';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';

const toast = useToast();
const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    system_prompt: {
        type: String,
        required: true,
    },
    chat: {
        type: Object,
    },
    messages: {
        type: Object,
    },
});

provide('system_prompt', props.system_prompt);

onMounted(() => {
    Echo.private(`collection.chat.${props.collection.data.id}.${props.chat.data.id}`)
    .listen('.status', (e) => {
        console.log(e);
        router.reload({
            preserveScroll: true,
        })
    })
    .listen('.update', (e) => {
        console.log(e);
        // Make a better ui for htis
        toast.success(e.updateMessage, {
                position: "bottom-right",
                timeout: 2000,
                closeOnClick: true,
                pauseOnFocusLoss: false,
                pauseOnHover: false,
                draggable: false,
                draggablePercent: 0.6,
                showCloseButtonOnHover: true,
                hideProgressBar: true,
                closeButton: "button",
                icon: true,
                rtl: false
            });
    });
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat with {{ collection.data.name }}
            </h2>
        </template>

        <div class="py-12">


            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Top area -->

                    <div class="border-b pb-5 px-3 py-4">
                        <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-semibold leading-6 text-gray-900">{{ collection.data.name }}</h3>
                            <p class="mt-2 max-w-4xl text-sm text-gray-500">
                                {{ collection.data.description }}
                            </p>

                        </div>

                        <SecondaryLink class="flex justify-between items-center gap-4" :href="route('collections.show', {
                    collection: collection.data.id,
                })">    <DocumentTextIcon class="h-5 w-5"></DocumentTextIcon>
                                
                            Back to Documents
                        </SecondaryLink>
                    </div>
                    <CollectionTags :collection="collection"></CollectionTags>

                    </div>
                    <div class="p-10">
                        <ChatUi :chat="chat" :messages="messages"></ChatUi>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

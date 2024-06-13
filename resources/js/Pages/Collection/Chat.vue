<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import {computed, onMounted, onUnmounted, provide, ref} from 'vue';
import Nav from '@/Pages/Collection/Components/Nav.vue';
import CollectionTags from './Components/CollectionTags.vue';
import { useDropzone } from "vue3-dropzone";
import { router, useForm } from '@inertiajs/vue3';
import FileUploader from './Components/FileUploader.vue';
import ChatUi from '@/Pages/Chat/ChatUi.vue';
import ChatSideNav from './Components/ChatSideNav.vue';
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
    chats: {
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
        router.reload({
            preserveScroll: true,
        })
    })
    .listen('.update', (e) => {
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

onUnmounted(() => {
    Echo.leave(`collection.chat.${props.collection.data.id}.${props.chat.data.id}`);
});

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat with {{ collection.data.name }}
            </h2>
        </template>

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-2">
                    <div class="hidden sm:col-span-2 sm:flex overflow-hidden">
                        <ChatSideNav
                            :collection="collection.data"
                            :chats="chats"></ChatSideNav>
                    </div>
                    <div class="col-span-12 sm:col-span-10">

                        <div class="overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="px-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-base font-semibold leading-6">{{ collection.data.name }}</h3>
                                        <p class="mt-2 max-w-4xl text-sm ">
                                            {{ collection.data.description }}
                                        </p>
                                    </div>
                                    <CollectionTags :collection="collection.data"></CollectionTags>
                                </div>
                            </div>
                            <ChatUi :chat="chat" :messages="messages"></ChatUi>
                        </div>
                    </div>

                    <div class="col-span-12 sm:hidden px-4 mt-4">
                        <ChatSideNav
                            :collection="collection.data"
                            :chats="chats"></ChatSideNav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

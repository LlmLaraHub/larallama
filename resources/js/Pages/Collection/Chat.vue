<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {computed, onMounted, onUnmounted, provide, nextTick, ref, watch} from 'vue';
import Nav from '@/Pages/Collection/Components/Nav.vue';
import CollectionTags from './Components/CollectionTags.vue';
import {router, useForm, usePage} from '@inertiajs/vue3';
import ChatSideNav from './Components/ChatSideNav.vue';
import { useToast } from 'vue-toastification';
import Chatv2 from "@/Pages/Chat/Chatv2.vue";

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
    reference_collections: {
        type: Object,
    },
    chats: {
        type: Object,
    },
    messages: {
        type: Object,
    },
});

const emittedMessages = ref([]);

const chatSubmitted = () => {
    emittedMessages.value = [];
}

provide('system_prompt', props.system_prompt);

onMounted(() => {
    if (currentChatId.value) {
        mountItems();
    }
});

const chatCreated = async () => {
    showChat.value = false;
    await nextTick();
    mountItems();
}

const showChat = ref(false);

const currentChatId = ref(props.chat?.data?.id);

watch(() => props.chat?.data?.id, (newId) => {
    if (newId !== currentChatId.value) {
        currentChatId.value = newId;
        mountItems();
    }
});

const mountItems = () => {
    Echo.leave(`collection.chat.${props.collection.data.id}.${props.chat.data.id}`);

    Echo.private(`collection.chat.${props.collection.data.id}.${props.chat.data.id}`)
        .listen('.status', (e) => {
            router.reload({
                preserveScroll: true,
            })
        })
        .listen('.update', (e) => {
            if (!emittedMessages.value.includes(e.updateMessage)) {
                emittedMessages.value.push(e.updateMessage);
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
            }
        });
    nextTick(() => {
        showChat.value = true;
    });
}
onUnmounted(() => {
    showChat.value = false;
    if (currentChatId.value) {
        Echo.leave(`collection.chat.${props.collection.data.id}.${currentChatId.value}`);
    }
});
</script>

<template>
    <AppLayout title="Dashboard">

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-2">
                    <div class="hidden sm:col-span-3 sm:flex overflow-hidden">
                        <ChatSideNav
                            @chatCreated="chatCreated"
                            :collection="collection.data"
                            :chats="chats"></ChatSideNav>
                    </div>
                    <div class="col-span-12 sm:col-span-9">
                        <div class="overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="px-3 mb-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-base font-semibold leading-6">
                                            <span class="text-gray-500">Collection</span>: {{ collection.data.name }}</h3>
                                        <p class="mt-1 mb-2 max-w-4xl text-sm italic truncate">
                                            {{ collection.data.description }}
                                        </p>
                                    </div>
                                    <CollectionTags :collection="collection.data"></CollectionTags>
                                </div>
                            </div>

                            <Chatv2
                                v-if="showChat"
                                    :chat="chat.data"
                                    :messages="messages.data"></Chatv2>
                            <div v-else>
                                <div class="flex w-full px-10 py-10 flex-col gap-4">
                                    <div class="skeleton h-32 w-full"></div>
                                    <div class="skeleton h-4 w-28"></div>
                                    <div class="skeleton h-4 w-full"></div>
                                    <div class="skeleton h-4 w-full"></div>
                                </div>
                            </div>
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

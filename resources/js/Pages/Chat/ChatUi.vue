<script setup>

import ChatMessage from "./ChatMessage.vue";
import {useToast} from "vue-toastification";
import {computed, onMounted, provide, ref} from "vue";
import ChatInputThreaded from "./ChatInputThreaded.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
const toast = useToast();

const props = defineProps({
    chat: Object,
    messages: Object,
})




</script>

<template>
    <div class="flex-1 flex flex-col bg-white shadow-sm mx-10 my-10 overflow-auto">
        <ChatMessage
            :chat="chat.data" :messages="messages.data">
            </ChatMessage>

            <ChatInputThreaded
            :chat="chat.data"
        ></ChatInputThreaded>
    </div>

</template>

<style scoped>

</style>

<script setup>

import ChatMessage from "./ChatMessage.vue";
import {computed, onMounted, provide, ref} from "vue";
import ChatInputThreaded from "./ChatInputThreaded.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";

const props = defineProps({
    chat: Object,
    messages: Object,
})




</script>

<template>
    <div class="flex-1 flex flex-col shadow-sm mx-2 sm:mx-5  overflow-hidden ">
            <div class="mb-2">
                <ChatMessage
                :chat="chat.data" :messages="messages.data">
                </ChatMessage>


            </div>

            <div class="mt-10">
                <ChatInputThreaded
            :chat="chat.data"
                ></ChatInputThreaded>
            </div>
    </div>

</template>

<style scoped>

</style>

<script setup>

import ChatBaloon from "./ChatBaloon.vue";
import {computed, inject, onMounted, onUnmounted, ref, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";

const props = defineProps({
    loading: {
        type: Boolean,
        default: true
    },
    messages: Object,
    chat: Object,
})

const toast = useToast();


</script>

<template>
    <div id="chat-messages"
         class="flex-col flex flex-grow h-full overflow-y-scroll gap-y-8 py-10 px-10
     ">
        <div v-if="chat?.id && messages.length === 0">
            Ask a question below to get started.
        </div>

        <div v-for="message in messages" v-else>
            <ChatBaloon :message="message" v-if="message.body_raw !== 'In Progress...'"></ChatBaloon>
        </div>
    </div>


</template>

<style scoped>

</style>

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
    assistant: Object,
    chat: Object,
})

const toast = useToast();

const chatType = ref('threaded')

const eventSource = ref({})

const waiting_on_run = ref(false)

const eventData = ref("")


const messages = ref([])

const getMessages = () => {
    axios.get(route("assistants.messages", {
        assistant: props.assistant.id
    })).then(data => {
        console.log(data.data);
        messages.value = data.data.messages;
    }).catch(error => {
        console.log("Error getting messages")
        console.log(error)
    })
}

const messagesComputed = computed(() => {
    if(messages.value.length === 0) {
        return props.chat?.messages;
    }

    return messages.value;
})

let echoChannel = ref(null); // keep track of the Echo channel

watch(() => props.chat?.id, (newVal, oldVal) => {
    if (newVal !== undefined && newVal !== oldVal) { // check if the id has a value and it's different from the previous one
        if(props.chat?.id) {
            echoChannel.value = Echo.private(`chat.user.${usePage().props.user.id}`)
                .listen('.threaded', (event) => {
                    getMessages();
                }).listen('.complete', (event) => {
                    getMessages();
                });
        } else {
            console.log("No chat id yet")
        }

    }
}, { immediate: true }); // { immediate: true } ensures that the watcher checks the initial value

onUnmounted(() => {
    if(echoChannel.value) {
        echoChannel.value.stopListening('.stream'); // stop listening when component is unmounted
    }
});


</script>

<template>
    <div id="chat-messages"
         class="flex-col-reverse flex flex-grow h-full overflow-y-scroll gap-y-8 max-h-[800px]
     ">
        <div v-if="chat?.id && messagesComputed.length === 0">
            Start: {{  chat.title }}
        </div>

        <div v-for="message in messagesComputed" v-else>
            <ChatBaloon :message="message" v-if="message.body_raw !== 'In Progress...'"></ChatBaloon>
        </div>
    </div>


</template>

<style scoped>

</style>

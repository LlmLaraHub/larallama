<script setup>

import ChatMessage from "./ChatMessage.vue";
import {useToast} from "vue-toastification";
import {computed, onMounted, provide, ref} from "vue";
import ChatInputThreaded from "./ChatInputThreaded.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
const toast = useToast();

const props = defineProps({
    chats: Object,
    copy: Object,
})

const chatType = ref('threaded')
const chat = ref({})
const assistant = ref({})
const messages = ref([])

const loading_assistant = ref(false)

const assistant_setup_running = ref(false)


provide('chat', chat.value)
provide('assistant', assistant.value)
provide('messages', messages.value)

Echo.private(`chat.user.${usePage().props.user.id}`)
    .listen('.status', (event) => {
        if(event.assistant.setup_status !== 'completed') {
            toast("Assistant status " + event.assistant.setup_status)
            loading_message.value = runningMessage();
        }
    }).listen('.complete', (event) => {
        assistant_setup_running.value = false;
});

const instantiateAssistant = () => {
    loading_assistant.value = true;

    messages.value = [];
    chat.value = {};
    assistant.value = {};

    axios.post(route('assistants.make'),
    {
        assistant_type: assistantType.value
    }).then(data => {
        console.log(data)
        assistant.value = data.data.assistant;
        chat.value = data.data.chat;
        messages.value = data.data.chat.messages;
        loading_assistant.value = false;


        if(assistant.value.setup_status === 'completed' || assistant.value.setup_status === 'default') {
            assistant_setup_running.value = false;
        } else {
            assistant_setup_running.value = true;
        }

        router.reload();


    }).catch(error => {
        console.log(error)
        toast.error("Error getting assistant please see logs")
        loading_assistant.value = false;
    })
}

const runningMessage = () => {
    let messages = [
        'If this is the first chat it might take a minute to set things up',
    ];
    // Generating a random index
    let randomIndex = Math.floor(Math.random() * messages.length);

    // Returning the message at the random index
    return messages[randomIndex];
}

const loading_message = ref(runningMessage())


const waiting_on_run = ref(false)

const reloadMessages = () => {
    waiting_on_run.value = true;
    router.reload();
}

const assistantType = ref("")

const makeAssistant = (type) => {
    console.log(type)
    assistantType.value = type;
    instantiateAssistant();
}

const previousChat = ref({})

const choosePreviousChat = () => {
    chat.value = previousChat.value;
    assistant.value = previousChat.value.assistant;
    messages.value = previousChat.messages;
    loading_assistant.value = false;
}

</script>

<template>
    <div class="flex-1 flex flex-col bg-white shadow-sm mx-10 my-10">

    <div class="mx-auto max-w-7xl min-h-[700px]">
     <div class="grid grid-cols-12">
        <div class="col-span-4 border-r-2 border-gray-100 py-5">
            <h2 class="mb-4 text-lg font-semibold">
                Choose a previous Chat or start a new one using the list below. 
            </h2>

            <div>
                <select 
                @change="choosePreviousChat"
                v-model="previousChat"
                class="select select-secondary w-full max-w-xs">
                <option disabled selected>Continue a previous chat</option>
                <option 
                :value="chat"
                v-for="chat in props.chats.data" :key="chat.id">
                {{  chat.title  }} #{{  chat.id }}
                </option>
            </select>
            </div>

            <div class=" text-gray-500 text-lg text-center mt-5">
                Or choose from the list below
            </div>

            <div class="flex flex-col gap-2 justify-center text-center mt-10 px-4 ">
                        <button 
                            v-for="assistant in usePage().props.assistants" :key="assistant.key"
                            type="button" 
                            class="btn btn-primary rounded-none text-lg h-20"
                            @click="makeAssistant(assistant.key)">
                                {{ assistant.label }}
                            </button>
            </div>
        </div>
        <div class="col-span-8 px-10 py-5">
            <ChatMessage
            :assistant="assistant"
            :chat="chat"
            v-if="chat?.id">
            </ChatMessage>
        <div
            v-auto-animate
            v-if="assistant_setup_running" class="flex justify-start gap-2 items-center text-gray-400 my-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
            </svg>

            <div class="flex justify-between items-center gap-2" v-auto-animate>
                {{ loading_message }}
                <span class="text-xs">id:{{assistant.id}}</span>
            </div>
        </div>
        <div v-if="loading_assistant" class="flex justify-center items-center gap-2 mt-10">
                
            <span class="text-lg text-gray-500 font-bold">Loading Chat</span>
            <span class="loading loading-dots loading-md text-gray-600" ></span>
        </div>
        <div class="mt-2">
            <ChatInputThreaded
            :loading="loading_assistant || assistant_setup_running"
            :assistant="assistant"
            :chat="chat"
            v-if="assistant?.id"
        ></ChatInputThreaded>
        </div>
        </div>
     </div>
    </div>

        
    </div>

</template>

<style scoped>

</style>

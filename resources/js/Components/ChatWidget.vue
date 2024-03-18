<template>
    <button
        class="items-center px-4 py-2 z-40
        absolute top-48 -right-20
        max-w-sm rotate-90
        flex mx-auto px-4 text-white flex justify-center gap-2 text-red-100
        bg-gradient-to-r from-red-600 to-red-800 h-10"
        type="button" @click="showChat = true" v-if="showChat === false">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
        </svg>
        show assistant chat
    </button>

    <div class="rounded-t flex flex-col flex-grow w-full justify-end gap-2 items-center bg-gradient-to-r from-red-600 to-red-800
    h-10 max-w-xl min-w-[400px]  absolute bottom-0 left-0 z-50"
         v-if="showChat">

        <div
            class="flex flex-col  w-full max-w-xl bg-white shadow-xl rounded-lg min-h-[700px] w-[400px]">

            <button
                class="
                bg-gradient-to-r from-red-600 to-red-800 h-10
                flex mx-auto w-full px-4 text-white flex justify-between gap-2 items-center text-red-100"
                type="button" @click="showChat = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
                hide
            </button>


            <div class="flex flex-col flex-grow h-0 p-4 overflow-auto w-[580px]">
                <div v-if="messages.length === 0">
                    no messages yet! Ask the ai system a question to get started with a new book
                    or help on a chapter.
                </div>
                <div v-else>
                    <div v-for="message in messages" :key="message">
                        <UserMessage :message="message" v-if="message.role === 'user'"></UserMessage>
                        <AiMessage :message="message" v-else></AiMessage>
                    </div>
                    <PendingMessage v-if="waitingOnResponse"/>
                </div>
            </div>
            <div class="bg-gray-300 p-4">
                <form @submit.prevent="submit">
                    <input
                        v-model="form.question"
                        class="flex items-center h-10 w-full rounded px-3 text-sm" type="text" placeholder="Type your message…">
                </form>
            </div>
        </div>
    </div>

</template>

<script setup>
import {onMounted, ref} from "vue"
import UserMessage from "./Partials/UserMessage.vue";
import AiMessage from "./Partials/AiMessage.vue";
import PendingMessage from "./Partials/PendingMessage.vue";
import {useForm} from "@inertiajs/vue3";
const showChat = ref(false)
import {useToast} from "vue-toastification";
const toast = useToast();

const messages = ref([])

const props = defineProps({
    user: Object
});

onMounted(() => {
    Echo.private(`messages.user.${props.user.id}`)
        .listen('.chat', (e) => {
            getMessages(false)
        })
    getMessages();
})

const form = useForm({
    question: ""
})

const waitingOnResponse = ref(false)

const submit = () => {

    form.post(route("messages.ask"), {
        preserveScroll: true,
        onSuccess: params => {
            form.question = "";
            getMessages(true);
        },
        onError: params => {
            toast.error("Sorry please try again shortly")
        }
    })

}


const getMessages = (waitingOnResponseState = false) => {
    axios.get(route("messages.index"))
        .then(data => {
            messages.value = data.data.messages;
            waitingOnResponse.value = waitingOnResponseState
        })
}

</script>

<style scoped>

</style>

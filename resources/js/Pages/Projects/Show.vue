<script setup>
import {Link, router, useForm} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import Kickoff from "@/Pages/Projects/Components/Kickoff.vue";
import InputError from "@/Components/InputError.vue";
import Clipboard from "@/Components/Clipboard.vue";
import Index from "@/Pages/Tasks/Index.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    campaign: Object,
    messages: Object
})

const form = useForm({
    input: ''
})

const chatCompleted = ref(false)

const chat = () => {
    form.post(route('chat.chat', {
        campaign: props.campaign.data.id
    }), {
        errorBag: 'chat',
        preserveScroll: true,
        onStart: () => {
            chatCompleted.value = false
        },
        onSuccess: () => {
            form.reset('input')
        },
        onFinish: () => {
            chatCompleted.value = true  // Set this to true when chat is completed
        }
    });
}

const dailyReportForm = useForm({})

const sendDailyReport = () => {
    dailyReportForm.post(route('daily-report.send', {
        campaign: props.campaign.data.id
    }), {
        errorBag: 'dailyReport',
        preserveScroll: true,
    });
}

</script>

<template>
    <AppLayout title="Campaign">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🚀 Campaign: {{ campaign.data.name}}


            </h2>

        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex justify-between p-4">
                        <div>
                            <div class="hidden sm:flex justify-between gap-2 ">
                                <span class="badge badge-accent">
                                    status: {{ campaign.data.status_formatted }}
                                </span>

                                <span class="badge badge-neutral">
                                    ai: {{ campaign.data.chat_status_formatted }}
                                 </span>

                                <span class="badge badge-secondary">
                                    start: {{ campaign.data.start_date }}
                                 </span>

                                <span class="badge badge-ghost">
                                    end: {{ campaign.data.end_date }}
                                 </span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 items-center">
                            <Kickoff :campaign="campaign.data"/>
                            <Link
                                :href="route('projects.edit', campaign.data.id)"
                                class="btn btn-primary rounded-none">Edit</Link>
                            <button @click="sendDailyReport"
                                    type="button"
                                    :disabled="dailyReportForm.processing"
                                    class="btn btn-outline rounded-none">
                                <span v-if="!dailyReportForm.processing">
                                    Send Daily Report
                                </span>
                                <span v-else class="loading loading-dots loading-sm"></span>
                            </button>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-12 p-4">
                        <div class="col-span-8">
                            <div v-auto-animate>
                                <template v-for="message in messages.data">
                                    <div class="border border-gray-300 rounded-md p-4 mb-4 overflow-scroll ">
                                        <div class="flex justify-end gap-2 items-center -mb-6">
                                            <span class="badge badge-ghost text-xs">{{ message.updated_at }}</span>
                                            <span class="badge badge-outline text-xs">{{ message.id }}</span>
                                            <Clipboard :content="message.content_raw"/>
                                        </div>
                                        <div  class="font-bold flex justify-start gap-2 items-center" v-if="message.role != 'user'">
                                            <div class="avatar placeholder">
                                                <div class="w-8 rounded-full bg-neutral text-neutral-content">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                                    </svg>
                                                </div>
                                            </div>

                                        </div>
                                        <div v-else class="font-bold flex justify-start gap-2 items-center">
                                            <div class="avatar">
                                                <div class="w-8 rounded-full">
                                                    <img :src="message.user?.profile_photo_url" />
                                                </div>
                                            </div>

                                        </div>
                                        <div class="prose" v-html="message.content"></div>

                                        <div class="flex justify-end gap-2 items-center">
                                            <Clipboard :content="message.content_raw"/>
                                        </div>
                                    </div>
                                </template>

                                <div>
                                    <Pagination :meta="messages" />
                                </div>

                            </div>
                        </div>
                        <div class="col-span-4 ml-2">

                            <form @submit.prevent="chat">
                                <InputError :message="form.errors.input" class="mt-2" />

                                <textarea
                                    v-model="form.input"
                                    placeholder="Ask a question..."
                                    required
                                    class="textarea textarea-bordered textarea-lg w-full"></textarea>
                                <div class="flex justify-end mt-2">
                                    <button
                                        :disabled="form.processing"
                                        class="btn btn-sm btn-secondary rounded-none" @click="chat">
                                        <span v-if="!form.processing">Chat</span>
                                        <span v-else class="loading loading-dots loading-lg"></span>
                                    </button>
                                </div>
                            </form>

                            <div>
                                <h2 class="font-bold flex justify-start gap-2 items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>

                                    Tasks</h2>

                                <Index
                                    :chat-completed="chatCompleted"
                                    :campaign="campaign.data"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>

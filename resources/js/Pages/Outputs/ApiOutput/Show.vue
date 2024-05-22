<script setup>
import { useToast } from 'vue-toastification';
import PageLayout from "@/Layouts/PageLayout.vue";
import Chat from "@/Pages/Outputs/WebPage/Components/Chat.vue";
import ChatSlideout from "@/Pages/Outputs/WebPage/Components/ChatSlideout.vue";
import {ref} from "vue";

const toast = useToast();

const props = defineProps({
    output: {
        type: Object
    },
    messages: Object
});

const showSlideout = ref(false)


</script>

<template>
    <PageLayout :title="output.title">
        <div class="mx-auto p-10">
            <div class="bg-white rounded-t-lg min-h-screen w-3/4 mx-auto shadow-lg">
                <div class="flex justify-start p-2">
                    <button
                        class="btn btn-primary flex justify-start gap-3 items-center"
                        type="button" @click="showSlideout = true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>

                        <span>chat</span>
                    </button>
                </div>
                <article class="prose prose-stone p-10">
                    <h1>{{ output.data.title }}</h1>

                    <div v-html="output.data.summary" class="prose prose-xl">
                    </div>
                </article>
            </div>
        </div>
        <ChatSlideout
            :messages="messages"
            :output="output.data"
            @close="showSlideout = false"
            :show="showSlideout">

        </ChatSlideout>
    </PageLayout>
</template>

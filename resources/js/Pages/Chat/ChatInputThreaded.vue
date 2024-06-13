<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import { ChevronDoubleDownIcon, ChevronRightIcon} from "@heroicons/vue/20/solid";
import {computed, inject, onMounted, onUnmounted, ref, watch} from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'
import Filters from "@/Pages/Collection/Components/Filters.vue";
import StyleGuide from "@/Pages/Collection/Components/StyleGuide.vue";


const toast = useToast();

const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    chat: Object,
})

const emits = defineEmits(['chatSubmitted'])

const errors = ref({})

const form = useForm({
    input: "",
    completion: false,
    tool: "",
    filter: null,
    persona: null
})

const filterChosen = ref({})
const personaChosen = ref({})

const filter = (filter) => {
    filterChosen.value = filter;
    form.filter = filter?.id
}

const persona = (persona) => {
    personaChosen.value = persona;
    form.persona = persona?.id;
}

const getting_results = ref(false)

onMounted(() => {
    Echo.private(`collection.chat.${props.chat.chatable_id}.${props.chat.id}`)
    .listen('.status', (e) => {
        router.reload({
            preserveScroll: true,
        })
    })
    .listen('.update', (e) => {
        if(e.updateMessage === 'Complete') {
            getting_results.value = false
            router.reload({
                preserveScroll: true,
            })
        }
    });
});

onUnmounted(() => {
    Echo.leave(`collection.chat.${props.chat.chatable_id}.${props.chat.id}`);
});


const save = () => {
    getting_results.value = true
    let message = form.input
    let completion = form.completion
    let filter = form.filter
    let tool = form.tool
    let persona = form.persona
    form.reset();
    axios.post(route('chats.messages.create', {
        chat: props.chat.id
    }), {
        input: message,
        completion: completion,
        tool: tool,
        persona: persona,
        filter: filter
    }).catch(error => {
        getting_results.value = false
        toast.error('An error occurred. Please try again.')
        console.log(error)
    });
}

const setQuestion = (question) => {
    form.input = question;
}


</script>

<template>
<div>
    <div v-if="getting_results">
            <div class="w-full px-10">
                <div class="animate-pulse flex space-x-4 mb-10">
                    <div class="flex-1 space-y-4 py-1">
                    <div class="h-6 bg-slate-400 rounded"></div>
                    <div class="space-y-3">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="h-6 bg-slate-400 rounded col-span-2"></div>
                                <div class="h-6 bg-slate-400 rounded col-span-1"></div>
                            </div>
                            <div class="h-6 bg-slate-400 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="w-full">

        <form @submit.prevent="save"  autocomplete="off"
              class="relative p-4 flex-col max-container mx-auto w-full" v-auto-animate>

            <div class="relative p-4 flex max-container mx-auto w-full" >
                <textarea
                rows="15"
                type="text"
                autofocus
                class="caret caret-pink-400 caret-opacity-50
                disabled:opacity-40
                bg-transparent block w-full border-0 py-1.5 ring-inset
                ring-secondary placeholder:text-gray-400 ring-2
                focus:ring-pink-500 sm:text-sm sm:leading-6"
                v-model="form.input" placeholder="Chat about your Collection"/>

                <span
                v-if="getting_results"
                class="mt-2">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>

                <button
                    v-else
                    :disabled="getting_results" type="submit"
                    class="
                    flex justify-start gap-3 items-center
                        bg-gray-850 hover:text-gray-400 text-gray-500 px-2.5 rounded-r-md">
                    <span
                        v-if="getting_results"
                        class="loading loading-dots loading-md"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"
                        fill="currentColor">
                        <path
                            d="M16.1 260.2c-22.6 12.9-20.5 47.3 3.6 57.3L160 376V479.3c0 18.1 14.6 32.7 32.7 32.7c9.7 0 18.9-4.3 25.1-11.8l62-74.3 123.9 51.6c18.9 7.9 40.8-4.5 43.9-24.7l64-416c1.9-12.1-3.4-24.3-13.5-31.2s-23.3-7.5-34-1.4l-448 256zm52.1 25.5L409.7 90.6 190.1 336l1.2 1L68.2 285.7zM403.3 425.4L236.7 355.9 450.8 116.6 403.3 425.4z"/>
                    </svg>
                </button>
            </div>
            <div class="
            justify-start gap-4 items-center ml-2">
                <div class="flex justify-start gap-2 items-center ml-1 mb-2">
                    <div v-if="filterChosen?.name" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary">
                            Filter being used: </span>
                        <span class="font-bold">{{filterChosen.name}}</span>
                        <button type="button" @click="filter({})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="personaChosen?.name" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary">
                            Persona being used: </span>
                        <span class="font-bold">{{personaChosen.name}}</span>
                        <button type="button" @click="persona({})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-start gap-2 items-center">

                    <Filters
                        @filter="filter"
                        :collection="chat.collection"></Filters>
                    <StyleGuide
                        @persona="persona"
                        :collection="chat.collection"></StyleGuide>
                </div>
            </div>

            <div>
                <h2
                class="text-lg font-medium prose mt-4 px-2 mb-4 items-center justify-start flex gap-4"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10
text-secondary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>

                    Click a button below to choose a focus for your chat. This will help the system to be more specific on how it integrates your Collection and the Prompt.</h2>
                <div class="flex justify-center items-center gap-3">
                        <div class="tooltip" data-tip="This will take your prompt and compare it to each document in your collection. The results will be the summary of all the comments the LLM has.">
                        <button
                            class="btn btn-secondary rounded-none"
                            :class="{ 'opacity-50': form.tool !== 'standards_checker' && form.tool !== '' }"
                        type="button"
                        @click="form.tool = 'standards_checker'"
                        >
                            Standards Checker
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                            </svg>

                        </button>
                        </div>

                        <div class="tooltip" data-tip="This is more like Chat GPT it will not search your collection. It will just take your prompt and the history of the chat and return the response.">
                            <button
                                class="btn btn-secondary rounded-none"
                                type="button"
                                :class="{ 'opacity-50': form.tool !== 'completion' && form.tool !== '' }"
                                @click="form.tool = 'completion'"
                            >
                                Raw Prompt
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                                </svg>

                            </button>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
</template>

<style scoped>

</style>

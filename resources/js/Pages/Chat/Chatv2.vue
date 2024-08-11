<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import {computed, inject, onMounted, onUnmounted, ref, watch} from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import Filters from "@/Pages/Collection/Components/Filters.vue";
import StyleGuide from "@/Pages/Collection/Components/StyleGuide.vue";
const toast = useToast();

import ScrollButton from "@/Components/ScrollButton.vue";

import ChatMessageV2 from "@/Pages/Chat/ChatMessageV2.vue";
import DisplayMenu from "@/Components/DisplayMenu.vue";
import {Switch, SwitchGroup, SwitchLabel} from '@headlessui/vue'
const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    chat: Object,
    messages: Object,
})


const emits = defineEmits(['chatSubmitted'])

const errors = ref({})

const form = useForm({
    input: "",
    completion: false,
    tool: "",
    filter: null,
    persona: null,
    reference_collection_id: null,
    date_range: null
})

const filterChosen = ref({})

const personaChosen = ref({})

const dateRangeChosen = ref({});

const referenceCollectionChosen = ref({});

const chatOnly = ref(false);

watch(chatOnly, () => {
    form.tool = chatOnly.value ? 'chat' : '';
})

const dateRangeSelected = (dateRange) => {
    dateRangeChosen.value = dateRange;
    form.date_range = dateRange?.id
}

const referenceCollectionSelected = (referenceCollection) => {
    referenceCollectionChosen.value = referenceCollection;
    form.reference_collection_id = referenceCollection?.id;
}

const filter = (filter) => {
    filterChosen.value = filter;
    form.filter = filter?.id
}

const persona = (persona) => {
    personaChosen.value = persona;
    form.persona = persona?.id;
}

const audience = (audience) => {
    form.input = form.input + "\n" + audience?.content;
    toast.info('Audience added to your prompt!', {
        position: 'bottom-right'
    });
}

const alreadyCompleted = ref(false);

const getting_results = ref(false)

const scrollButton = ref(null)
const bottomTarget = ref(null)

onMounted(() => {

    if (scrollButton.value) {
        scrollButton.value.bottomTarget = bottomTarget.value
    }

    chatMessages.value = props.messages;
    Echo.private(`collection.chat.${props.chat.chatable_id}.${props.chat.id}`)
        .listen('.status', (e) => {

        })
        .listen('.update', (e) => {
            if(e.updateMessage === 'Complete') {
                getLatestMessage();
                getting_results.value = false
                alreadyCompleted.value = true;
                rerunning.value = false;
            } else if(e.updateMessage === 'Running') {
                getting_results.value = true
            }
        });
});

onUnmounted(() => {
    chatMessages.value = [];
    Echo.leave(`collection.chat.${props.chat.chatable_id}.${props.chat.id}`);
});

const getLatestMessage = (marketCompleted = true) => {
    axios.get(route('chats.collection.latest', {
        collection: props.chat.chatable_id,
        chat: props.chat.id
    })).then(response => {
        chatMessages.value = response.data.messages
    })
}


const chatMessages = ref([]);

const save = () => {
    emits('chatSubmitted')

    // NOTE: Why did I not just use form?
    // I think there was a reload limitations but now I am doing get message
    // @TODO just go back to using form
    getting_results.value = true
    let message = form.input
    let completion = form.completion
    let filter = form.filter
    let tool = form.tool
    let persona = form.persona
    let date_range = form.date_range
    let reference_collection_id = form.reference_collection_id
    form.reset({
        input: message,
    });

    form.errors = [];
    alreadyCompleted.value = false;
    axios.post(route('chats.messages.create', {
        chat: props.chat.id
    }), {
        input: message,
        completion: completion,
        tool: tool,
        date_range: date_range,
        reference_collection_id: reference_collection_id,
        persona: persona,
        filter: filter
    }).then(response => {
        getLatestMessage(false);
        form.input = "";
    })
        .catch(error => {
            getting_results.value = false
            toast.error('An error occurred. Please try again.')
            console.log(error)
            form.errors = error.response.data.errors;
        });
}

const setQuestion = (question) => {
    form.input = question;
}

const reusePrompt = (prompt) => {
    form.input = prompt;
    router.visit('#chat-input', {
        preserveState: true,
    });
    toast.success('Prompt ready to reuse', {
        position: "bottom-right",
    });
}

const rerunForm = useForm({});

const rerunning = ref(false);

const rerun = (message) => {
    rerunning.value = true;
    rerunForm.post(route('messages.rerun', {
        message: message.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            //emits('rerun');
        }
    });
}

</script>

<template>
    <div class="flex-1 flex flex-col mx-auto px-5 ">
        <ScrollButton ref="scrollButton" />
        <div v-if="chat?.id && chatMessages.length === 0">
            Ask a question below to get started.
        </div>
        <div v-for="message in chatMessages" v-else v-auto-animate>
            <ChatMessageV2
                @reusePrompt="reusePrompt"
                @rerun="rerun"
                :message="message">
                <template #rerun>
                    <button type="button" class="btn btn-ghost" @click="rerun(message)">
                        <svg
                            :class="{ 'animate-spin': rerunning }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Retry
                    </button>
                </template>
            </ChatMessageV2>
        </div>

        <div v-if="getting_results">
            <div class="w-full py-5">
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
    </div>
    <div class="join join-vertical w-full
                    mt-4 p-2 mb-10 pb-10">
        <form @submit.prevent="save"  autocomplete="off">
            <div class="join-item">
                <!-- file upload will go here -->
            </div>

            <div class="w-full mx-auto px-4">
                    <div class="label">
                        <div class="border border-secondary rounded-md p-4">
                            <SwitchGroup>
                                <h2 class="text-lg font-medium leading-6 flex items-center justify-between gap-2 my-4 text-secondary">
                                    <div class="flex items-center gap-2 justify-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                                        </svg>
                                        <span>
                                    Enable Chat Only
                                    </span>
                                    </div>

                                    <Switch
                                        as="button"
                                        v-model="chatOnly"
                                        :class="form.tool !== 'chat' ? 'bg-neutral' : 'bg-secondary'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full"
                                    >
                                        <span class="sr-only">Enable notifications</span>
                                        <span
                                            :class="form.tool === 'chat' ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                                        />
                                    </Switch>
                                </h2>

                                <SwitchLabel class="mr-4 prose">
                                    <p>
                                        The system defaults to chatting with your Collection. But sometimes
                                        you might want to just chat with this thread and what is in your prompt.
                                        Check this to work that way. You will still have some tools like "search_the_web
                                    </p>
                                </SwitchLabel>
                            </SwitchGroup>
                        </div>
                    </div>
                    <div ref="bottomTarget"></div>

                    <div class="flex mt-5 ">
                        <!--                    add anchor below so I cal scroll to it-->
                        <textarea
                            id="chat-input"
                            v-model="form.input"
                            class="
                        textarea textarea-bordered h-40 text-lg w-full"
                            placeholder="Chat with your Collection">

                    </textarea>

                        <div class="flex justify-center mb-2 pt-1 mx-auto ml-4">
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
                                :disabled="getting_results || !form.input" type="submit"
                                class="btn btn-secondary rounded-md btn-sm">
                            <span
                                v-if="getting_results"
                                class="loading loading-dots loading-md">

                            </span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                     class="size-4 font-bold ">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                <div v-if="form.errors.length > 0" class="mt-2 text-sm text-red-600">
                    <h2 class="text-primary"> Errors:</h2>
                    <div v-for="formError in form.errors" :key="formError">
                        <div v-for="error in formError" :key="error" class="italic text-sm text-red-600">
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="join-item px-4 mt-4">
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
                    <div v-if="dateRangeChosen?.name" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary">
                            Date Range: </span>
                        <span class="font-bold">{{dateRangeChosen.name}}</span>
                        <button type="button" @click="dateRangeSelected({})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="referenceCollectionChosen?.name" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary">
                            Reference: </span>
                        <span class="font-bold">{{referenceCollectionChosen.name}}</span>
                        <button type="button" @click="referenceCollectionSelected({})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-start gap-2 items-center">

                    <Filters
                        v-if="form.tool !== 'chat'"
                        @filter="filter"
                        :collection="chat.collection"></Filters>
                    <StyleGuide
                        @persona="persona"
                        @audience="audience"
                        :collection="chat.collection">
                    </StyleGuide>

                    <DisplayMenu
                        v-if="form.tool !== 'chat'"
                        :items="usePage().props.date_ranges" @itemSelected="dateRangeSelected">
                        <template #title>
                            Date Range
                        </template>
                    </DisplayMenu>

                    <DisplayMenu
                        v-if="form.tool === 'reporting_tool'"
                        :search="true"
                        :items="usePage().props.reference_collections"
                        @itemSelected="referenceCollectionSelected">
                        <template #title>
                            <div class="flex gap-2 items-center">
                                Reference Collection
                                <div class="tooltip tooltip-info"
                                     data-tip="Can be used with Reporting Tool to Create Solutions from the Reference Collection">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </DisplayMenu>
                </div>

                <div
                    v-if="form.tool !== 'chat'" v-auto-animate>
                    <h2
                        class="text-lg font-medium prose mt-4 px-2 mb-4 items-center justify-start flex gap-4"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10
text-secondary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>

                        Click a button below to choose a focus for your chat. This will help the system to be more specific on how it integrates your Collection and the Prompt.</h2>
                    <div class="flex justify-center items-center gap-3 flex-wrap mx-auto">
                        <template v-for="tool in usePage().props.tools" :key="tool.name">
                            <div class="tooltip tooltip-info"
                                 :data-tip="tool.description">
                                <button
                                    class="btn btn-secondary rounded-none"
                                    :class="{ 'opacity-50': form.tool !== tool.name && form.tool !== '' }"
                                    type="button"
                                    @click="form.tool = tool.name"
                                >
                                    {{ tool.name_formatted}}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                                    </svg>

                                </button>
                            </div>
                        </template>

                        <div class="tooltip tooltip-info"
                             data-tip="Clear tool choice">
                            <button
                                type="button"
                                class="btn btn-ghost rounded-none"
                                @click="form.tool = ''"
                            >
                                Clear Choice
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</template>

<style scoped>

</style>

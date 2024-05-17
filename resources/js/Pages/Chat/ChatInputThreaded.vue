<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import { ChevronDoubleDownIcon, ChevronRightIcon} from "@heroicons/vue/20/solid";
import {computed, inject, onMounted, onUnmounted, ref, watch} from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'
import Filters from "@/Pages/Collection/Components/Filters.vue";


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
    filter: null
})

const filterChosen = ref({})

const filter = (filter) => {
    filterChosen.value = filter;
    form.filter = filter?.id
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
        // Make a better ui for htis
        console.log("chat reuslts came in")
        console.log(e)
        if(e.updateMessage === 'Complete') {
            getting_results.value = false
            router.reload({
                preserveScroll: true,
            })
        }
    });
});



const save = () => {
    getting_results.value = true
    let message = form.input
    let completion = form.completion
    let filter = form.filter
    form.reset();
    axios.post(route('chats.messages.create', {
        chat: props.chat.id
    }), {
        input: message,
        completion: completion,
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
    <div class="w-full bg-gray-50">

        <form @submit.prevent="save"  autocomplete="off"
              class="relative p-4 flex-col max-container mx-auto w-full" v-auto-animate>

            <div class="relative p-4 flex max-container mx-auto w-full" >
                <textarea
                rows="2"
                type="text"
                autofocus="true"
                class="caret caret-indigo-400 caret-opacity-50
                disabled:opacity-40
                bg-transparent block w-full border-0 py-1.5 ring-0 ring-inset
                ring-indigo-500 placeholder:text-gray-400 focus:ring-2
                focus:ring-indigo-500 sm:text-sm sm:leading-6"
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
            <div class="flex justify-between">
                <div>
                    <SwitchGroup
                        v-if="!usePage().props.settings.supports_functions"
                        as="div" class="flex items-center">
                        <Switch v-model="form.completion" :class="[form.completion ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']">
                            <span aria-hidden="true" :class="[form.completion ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
                        </Switch>
                        <SwitchLabel as="span" class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Completion</span>
                            {{ ' ' }}
                            <span class="text-gray-500">(Your LLM does not support functions)</span>
                        </SwitchLabel>
                    </SwitchGroup>
                </div>

                <div class="flex justify-start gap-2 items-center">
                    <div v-if="filterChosen?.name" class="flex justify-start gap-1 items-center">
                        <span class="text-gray-600">
                            Filter being used: </span>
                        <span class="font-bold">{{filterChosen.name}}</span>
                        <button type="button" @click="filter({})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                <Filters
                    @filter="filter"
                    :collection="chat.collection"></Filters>
                </div>
            </div>


        </form>
    </div>
</div>
</template>

<style scoped>

</style>

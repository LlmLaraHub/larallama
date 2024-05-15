<script setup>

import {router, useForm, usePage} from "@inertiajs/vue3";
import {Switch, SwitchGroup, SwitchLabel} from "@headlessui/vue";
import {useToast} from "vue-toastification";
import {onMounted, ref} from "vue";
import axios from "axios";
import ChatMessages from "@/Pages/Outputs/WebPage/Components/ChatMessages.vue";

const toast = useToast();

const emits = defineEmits(['chatSubmitted'])


const props = defineProps({
    output: Object,
    messages: Object
})


const errors = ref({})

const form = useForm({
    input: "",
    completion: false
})

const getting_results = ref(false)


const chat = () => {
    getting_results.value = true
    toast.info("Sending chat to llm")
    form.post(
        route('collections.outputs.web_page.chat', {
            output: props.output.id
        }), {
            preserveScroll: true,
            onSuccess: params => {
                form.reset()
                toast.info("See Response")
                getting_results.value = false;
            },
            onError: params => {
                toast.error("Error getting chat reply");
                getting_results.value = false;
            }
        }
    );
}
</script>

<template>
    <div class="w-full">
        <div class="flex-1 flex flex-col bg-white shadow-sm mx-10 my-10 overflow-auto border">
            <div class="mb-2">
                <ChatMessages :messages="messages"/>
            </div>

            <div class="mt-10">
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

                        <form @submit.prevent="chat" autocomplete="off"
                              class="relative p-4 flex-col max-container mx-auto w-full" v-auto-animate>

                            <div class="relative p-4 flex max-container mx-auto w-full">
                <textarea
                    rows="2"
                    type="text"
                    class="caret caret-indigo-400 caret-opacity-50
                    border border-indigo-500
                disabled:opacity-40
                bg-transparent block w-full py-1.5 ring-0 ring-inset
                ring-indigo-500 placeholder:text-gray-600 focus:ring-2
                text-gray-800
                focus:ring-indigo-500 sm:text-sm sm:leading-6"
                    v-model="form.input" placeholder="Chat about your Collection"/>

                                <span
                                    v-if="getting_results"
                                    class="mt-2">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>

<style scoped>

</style>

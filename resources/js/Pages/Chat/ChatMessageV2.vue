<script setup>

import { TabGroup, TabList, Tab, TabPanels, TabPanel, TransitionRoot } from '@headlessui/vue'
import ReferenceTable from './Components/ReferenceTable.vue'
import History from './Components/History.vue'
import Clipboard from "@/Components/ClipboardButton.vue";
import {useForm} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";
import Report from "@/Pages/Chat/Components/Report.vue";
import MessageMetaData from "@/Pages/Chat/Components/MessageMetaData.vue";
import DeleteMessage from "@/Pages/Chat/Components/DeleteMessage.vue";

const props = defineProps({
    message: Object
})


const emits = defineEmits(['reusePrompt'])

const reuse = (prompt) => {
    emits('reusePrompt', prompt)
}
</script>

<template>

    <div class="flex flex-col gap-4 w-full">
        <div>
            <div class="text-xs mt-2 ml-1 mb-1 w-full flex gap-x-2">
                <span class="font-semibold " v-if="!message.from_ai">
                    {{ message.initials}}
                </span>
                <span class="text-primary" v-if="!message.from_ai">
                    {{ message.diff_for_humans }}
                </span>
            </div>
            <div v-if="message.from_ai" class="w-full">
                    <TabGroup >
                        <TabList class="flex justify-start gap-4 items-center ml-2 mb-2" >
                            <Tab as="div" v-slot="{ selected }">
                                <button type="button" :class="{ 'btn-outline rounded-none': selected }" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                                    </svg>

                                    Message
                                </button>
                            </Tab>
                            <Tab as="div" v-slot="{ selected }"  class="disabled:opacity-45 disabled:cursor-not-allowed">
                                <button type="button" :class="{ 'btn-outline rounded-none': selected }" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                    </svg>
                                    <span :class="{ 'underline': selected }">Sources</span>
                                </button>
                            </Tab>
                            <Tab as="div" v-slot="{ selected }">
                                <button type="button" :class="{ 'btn-outline rounded-none': selected }" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                    </svg>

                                    Prompt History</button>
                            </Tab>
                            <Tab as="div" v-slot="{ selected }">
                                <button type="button" :class="{ 'btn-outline rounded-none': selected }" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>
                                  Report (BETA)
                                </button>
                            </Tab>
                        </TabList>
                        <TabPanels  v-auto-animate>
                            <TabPanel>
                                <div class="
                            bg-neutral/10
                            mx-auto
                            w-full
                            flex-col rounded-md relative shadow-lg shadow-inner-custom  p-4 ">
                                    <div class="justify-end flex text-xs text-gray-500">
                                        #{{ message.id}}
                                    </div>
                                    <div class="prose"
                                         v-html="message.body_markdown">
                                    </div>
                                    <div class="w-full flex justify-between gap-2 items-center">
                                        <div class="w-full">
                                            <MessageMetaData
                                                @reusePrompt="reuse"
                                                :message="message"></MessageMetaData>
                                        </div>
                                        <div class="flex justify-end gap-2 items-center">
                                            <slot name="rerun"></slot>
                                            <Clipboard
                                                class="btn-ghost"
                                                :content="message.body">
                                                Copy
                                            </Clipboard>
                                        </div>

                                    </div>
                                </div>
                            </TabPanel>
                            <TabPanel>
                                <div class="w-full p-4 mt-2 shadow-lg rounded-md">
                                    <div>
                                        <div class="overflow-x-auto">
                                            <ReferenceTable :message="message" />
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                            <TabPanel>
                                <div class="w-fullp-4 mt-2 shadow-lg rounded-md">
                                    <div>
                                        <div class="overflow-x-auto">
                                            <History :message="message" />
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                            <TabPanel>
                                <div class="w-fullp-4 mt-2 shadow-lg rounded-md">
                                    <div>
                                        <div class="overflow-x-auto">
                                            <Report :message="message" />
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </TabGroup>
            </div>
            <div v-else
                class="flex-col rounded-md shadow-lg p-4 border-neutral border mb-4">
                <div class="justify-end flex text-xs text-gray-500 -mb-5">
                    #{{ message.id}}
                </div>

                <div class="grow leading-loose prose" v-html="message.body_markdown">
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <MessageMetaData :message="message"/>
                    </div>
                    <div class="flex justify-end items-center -mb-2 gap-2 ">
                        <button type="button" class="btn btn-sm btn-ghost" @click="reuse(message.body)">
                            <span>Reuse Prompt</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </button>
                        <DeleteMessage  :message="message"></DeleteMessage>
                    </div>
                </div>
            </div>


        </div>


    </div>

</template>

<style scoped></style>

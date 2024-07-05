<script setup>

import { TabGroup, TabList, Tab, TabPanels, TabPanel, TransitionRoot } from '@headlessui/vue'
import ReferenceTable from './Components/ReferenceTable.vue'
import History from './Components/History.vue'
import Clipboard from "@/Components/ClipboardButton.vue";
import {useForm} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";

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
                                <div :class="{ 'underline': selected }" class="hover:cursor-pointer m4-2">Message</div>
                            </Tab>
                            <Tab as="div" v-slot="{ selected }"  class="disabled:opacity-45 disabled:cursor-not-allowed">
                                <div
                                    class="hover:cursor-pointer flex justify-start gap-2 items-center">
                                    <span :class="{ 'underline': selected }">Sources</span>
                                    <div class="badge badge-primary">{{ message?.message_document_references.length}}</div>
                                </div>
                            </Tab>
                            <Tab as="div" v-slot="{ selected }">
                                <div :class="{ 'underline': selected }" class="hover:cursor-pointer m4-2">Prompt History</div>
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
                                    <div class="w-full flex justify-end gap-2 items-center">

                                        <slot name="rerun"></slot>
                                        <Clipboard
                                            class="btn-ghost"
                                            :content="message.body">
                                            Copy
                                        </Clipboard>
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
                        </TabPanels>
                    </TabGroup>
            </div>
            <div v-else
                class="flex-col rounded-md shadow-lg p-4 border-neutral border mb-4">
                <div class="justify-end flex text-xs text-gray-500">
                    #{{ message.id}}
                </div>
                <div class="grow leading-loose prose" v-html="message.body_markdown">
                </div>

                <div class="w-full flex justify-end gap-2 items-center">
                    <button type="button" class="btn btn-ghost" @click="reuse(message.body)">
                        <span>Reuse Prompt</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </button>
                </div>
                <div class="flex justify-start">
<!--                    {{ message.meta_data}}-->
                </div>
            </div>


        </div>


    </div>

</template>

<style scoped></style>

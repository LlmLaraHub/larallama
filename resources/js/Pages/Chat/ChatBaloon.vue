<script setup>

import { TabGroup, TabList, Tab, TabPanels, TabPanel, TransitionRoot } from '@headlessui/vue'
import ReferenceTable from './Components/ReferenceTable.vue'
import History from './Components/History.vue'

const props = defineProps({
    message: Object
})
</script>

<template>


    <div class="message-container mx-auto max-container flex items-start gap-x-4"
        :class="message.from_ai ? 'flex-row-reverse' : 'flex-row'">


        <div class="flex-shrink-0 hidden md:block">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-300">
                <span class="text-xs font-medium leading-none text-white">
                    {{ message.initials }}
                </span>
            </span>
        </div>

        <div>

            <div class="text-xs mb-1 w-full flex gap-x-2" :class="message.from_ai ? 'flex-row-reverse' : ''">
                <span class="font-semibold text-gray-300" v-if="message.from_ai">
                    Ai
                </span>
                <span class="font-semibold text-gray-300" v-else>
                    You
                </span>
                <span class="text-gray-500">
                    {{ message.diff_for_humans }}
                </span>
            </div>
            <div v-if="message.from_ai">
                <TabGroup >
                    <TabList class="flex justify-start gap-4 items-center">
                        <Tab as="div" v-slot="{ selected }">
                            <div :class="{ 'underline text-gray-800': selected }" class="hover:cursor-pointer m4-2 text-gray-500">Message</div>
                        </Tab>
                        <Tab as="div" v-slot="{ selected }"  class="disabled:opacity-45 disabled:cursor-not-allowed">
                            <div :class="{ 'underline text-gray-800': selected }" class="hover:cursor-pointer
                            text-gray-500 flex justify-start gap-2 items-center">
                                <span>Sources</span> <div class="text-xs text-white rounded-full bg-indigo-600 h-4 w-6 text-center">{{ message?.message_document_references.length}}</div>
                            </div>
                        </Tab>
                        <Tab as="div" v-slot="{ selected }">
                            <div :class="{ 'underline text-gray-800': selected }" class="hover:cursor-pointer m4-2 text-gray-500">Prompt History</div>
                        </Tab>
                    </TabList>
                    <TabPanels  v-auto-animate>
                        <TabPanel>
                            <div class="message-baloon flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                                :class="message.from_ai ? 'bg-gray-300/10 rounded-tr-none border-indigo-500' : 'flex-row-reverse'">

                                <div v-if="message.type !== 'image'" class="message-content grow "
                                    :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'"
                                    v-html="message.body_markdown">
                                </div>

                                <div v-if="message.type === 'image'">
                                    <img class="max-w-2xl" :src="message.file_url" />
                                </div>
                            </div>
                        </TabPanel>
                        <TabPanel>
                            <div class="min-w-full border border-gray-400 p-4 mt-2 shadow-lg rounded-md">
                                <div>
                                    <div class="overflow-x-auto">
                                        <ReferenceTable :message="message" />
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                        <TabPanel>
                            <div class="min-w-full border border-gray-400 p-4 mt-2 shadow-lg rounded-md">
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
                class="message-baloon flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                :class="message.from_ai ? 'bg-gray-300/10 rounded-tr-none border-indigo-500' : 'flex-row-reverse'">

                <div v-if="message.type !== 'image'" class="message-content grow "
                    :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'" v-html="message.body_markdown">
                </div>

                <div v-if="message.type === 'image'">
                    <img class="max-w-2xl" :src="message.file_url" />
                </div>
            </div>


        </div>


    </div>

</template>

<style scoped></style>

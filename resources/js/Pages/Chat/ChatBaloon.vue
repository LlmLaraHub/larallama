<script setup>

import { TabGroup, TabList, Tab, TabPanels, TabPanel, TransitionRoot } from '@headlessui/vue'
import ReferenceTable from './Components/ReferenceTable.vue'
import History from './Components/History.vue'

const props = defineProps({
    message: Object
})
</script>

<template>


    <div class="mx-auto max-container flex items-start gap-x-4"
        :class="message.from_ai ? 'flex-row-reverse' : 'flex-row'">

        <div>
            <div class="text-xs mb-1 w-full flex gap-x-2" :class="message.from_ai ? 'flex-row-reverse' : ''">
                <span class="font-semibold" v-if="message.from_ai">
                    Ai
                </span>
                <span class="font-semibold " v-else>
                    User
                </span>
                <span class="text-primary">
                    {{ message.diff_for_humans }}
                </span>
            </div>
            <div v-if="message.from_ai">
                <TabGroup >
                    <TabList class="flex justify-start gap-4 items-center">
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
                            flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                                :class="message.from_ai ? 'rounded-tr-none' : 'flex-row-reverse'">

                                <div class="grow"
                                    :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'"
                                    v-html="message.body_markdown">
                                </div>
                            </div>
                        </TabPanel>
                        <TabPanel>
                            <div class="min-w-full p-4 mt-2 shadow-lg rounded-md">
                                <div>
                                    <div class="overflow-x-auto">
                                        <ReferenceTable :message="message" />
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                        <TabPanel>
                            <div class="min-w-full p-4 mt-2 shadow-lg rounded-md">
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
                class="bg-base-100 flex rounded-md shadow-lg shadow-inner-custom  p-4 prose "
                :class="message.from_ai ? 'rounded-tr-none' : 'flex-row-reverse'">
                <div class="grow"
                     :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'"
                     v-html="message.body_markdown">
                </div>

                <div v-if="message.type === 'image'">
                    <img class="max-w-2xl" :src="message.file_url" />
                </div>
            </div>


        </div>


    </div>

</template>

<style scoped></style>

<script setup>

const props = defineProps({
    messages: Object
})
</script>

<template>

    <div id="chat-messages"
         class="flex-col flex flex-grow h-full overflow-y-scroll gap-y-8 py-10 px-2
     ">
        <div v-if="messages.length === 0" class=" text-gray-900 flex justify-start gap-2 items-center">
            <div>Ask a question below to get started.</div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 15-6 6m0 0-6-6m6 6V9a6 6 0 0 1 12 0v3" />
            </svg>

        </div>

        <div v-for="message in messages" v-else>
            <div class="message-container mx-auto max-container flex items-start gap-x-4"
                 :class="message.is_ai ? 'flex-row-reverse' : 'flex-row'">
            </div>

            <div>
                <div class="text-xs mb-1 w-full flex gap-x-2" :class="message.is_ai ? 'flex-row-reverse' : ''">
                    <div class="avatar placeholder" v-if="message.is_ai">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                            <span class="text-sm">llm</span>
                        </div>
                    </div>
                    <div class="avatar placeholder" v-else>
                        <div class="bg-accent text-neutral-content rounded-full w-8">
                            <span class="text-xs">you</span>
                        </div>
                    </div>
                </div>

                <div v-if="message.is_ai">
                    <div class="message-baloon flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                         :class="message.is_ai ? 'bg-gray-300/10 rounded-tr-none border-indigo-500' : 'flex-row-reverse'">
                        <div v-if="message.type !== 'image'" class="message-content grow"
                             :class="message.role === 'assistant' ? 'rounded-tr-none' : 'rounded-tl-none'"
                             v-html="message.content">
                        </div>
                    </div>
                </div>

                <div v-else
                     class="message-baloon flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                     :class="message.role === 'assistant' ? 'bg-gray-300/10 rounded-tr-none border-indigo-500' : 'flex-row-reverse'">

                    <div v-if="message.type !== 'image'" class="message-content grow prose"
                         :class="message.role === 'assistant' ? 'rounded-tr-none' : 'rounded-tl-none'" v-html="message.content">
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>

</style>

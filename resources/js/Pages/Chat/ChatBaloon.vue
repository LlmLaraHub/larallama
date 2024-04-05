<script setup>

import LightIndicator from "./LightIndicator.vue";

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
                    {{  message.initials }}
                </span>
            </span>
        </div>

        <div>

            <div class="text-xs mb-1 w-full flex gap-x-2"
                 :class="message.from_ai ? 'flex-row-reverse' : ''"
            >
                <span class="font-semibold text-gray-300" v-if="message.from_ai">
                    Ai
                </span>
                <span class="font-semibold text-gray-300" v-else>
                    You
                </span>


                <span
                    class="text-gray-500">
                    {{message.diff_for_humans}}
                </span>
            </div>

            <div class="message-baloon flex rounded-md relative shadow-lg shadow-inner-custom  p-4 prose space-y-4l "
                 :class="message.from_ai ? 'bg-gray-300/10 rounded-tr-none border-indigo-500' : 'flex-row-reverse'">

                <div

                    v-if="message.type !== 'image'"
                    class="message-content grow "
                    :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'"
                v-html="message.body_markdown"
                >
                </div>

                <div v-if="message.type === 'image'">
                    <img class="max-w-2xl" :src="message.file_url"/>
                </div>
            </div>

        </div>


    </div>

</template>

<style scoped>

</style>

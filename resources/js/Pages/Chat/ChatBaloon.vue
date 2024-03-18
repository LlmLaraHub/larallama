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
            <img 
            v-if="message.role.assistant"
            loading="lazy" class="inline-block h-10 w-10 rounded-full shadow-lg"
                 :class="message.from_ai ? 'shadow-rose-600/30' : ''"
                 :src="message.from_ai ? '/assets/ai-icon.svg' : '/assets/user-icon.svg'"
                 alt="">

            <span v-else class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-500">
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

            <div class="message-baloon flex rounded-md relative shadow-lg"
                 :class="message.from_ai ? 'bg-gray-500/10 rounded-tr-none border-rose-500' : 'bg-black/20 rounded-tl-none flex-row-reverse'">

                <LightIndicator :primary="message.from_ai"></LightIndicator>

                <div

                    v-if="message.type !== 'image'"
                    class="message-content grow shadow-inner-custom shadow-lg p-4 rounded space-y-4"
                    :class="message.from_ai ? 'rounded-tr-none' : 'rounded-tl-none'"
                v-html="message.body"
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

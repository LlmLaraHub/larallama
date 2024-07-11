<script setup>

const props = defineProps({
    message: Object
})
</script>

<template>
    <div class="flex justify-between items-center -mb-2">
        <div class="flex justify-start text-sm gap-2 items-center w-3/4">
            <div v-if="message.meta_data?.persona" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Persona being used: </span>
                <span class="font-bold">{{message.meta_data.persona}}</span>
            </div>
            <div v-if="message.meta_data?.filter" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Filter being used: </span>
                <span class="font-bold">{{message.meta_data.filter.name}}</span>
            </div>
            <div v-if="message.meta_data?.driver" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            LLM Used: </span>
                <span class="font-bold">{{message.meta_data.driver}}</span>
            </div>
            <div v-if="message.meta_data?.date_range" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Date Range used: </span>
                <span class="font-bold">{{message.meta_data.date_range}}</span>
            </div>

            <div v-if="message.meta_data?.tool" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Tool used: </span>
                <span class="font-bold">{{message.meta_data.tool}}</span>
            </div>

            <div v-if="message.tools?.tools?.length > 0" class="flex justify-start gap-2 items-center"

            >
                        <span class="text-secondary">
                            Tools used: </span>
                        <span
                            v-for="tool in message.tools.tools" :key="tool.function_name"
                            class="font-bold">{{tool.function_name}}</span>
            </div>
        </div>

        <div class="w-full flex justify-end gap-2 items-center" v-if="message.from_ai === false">
            <button type="button" class="btn btn-ghost" @click="reuse(message.body)">
                <span>Reuse Prompt</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>

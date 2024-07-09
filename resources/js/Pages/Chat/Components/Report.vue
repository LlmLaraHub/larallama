<template>
    <div class="p-10">
        <h2>Reporting sections surfaced from the collection</h2>
        <div v-if="message.report?.sections.length === 0">
            No sections read more <a href="https://docs.larallama.io/docs/reporting" target="_blank">here</a>
            on building reports
        </div>
        <div v-for="section in message.report?.sections" class="border border-neutral rounded-md p-4 mt-4">
            <h3 class="font-bold">Section ID: {{ section.id }} - {{ section.subject }}</h3>
            <div class="prose" v-html="section.content"></div>
            <div class="flex justify-end gap-2 items-center text-sm text-gray-500">
                <div> sort: {{ section.sort_order + 1 }}</div>
            </div>
        </div>
    </div>
</template>
<script setup>
import {computed} from "vue";
import Clipboard from "@/Components/ClipboardButton.vue";

const props = defineProps({
    message: Object
})

const allPrompt = computed(() => {
    return props.message.prompt_histories_plain.map(prompt => prompt.prompt_plain).join('\n\n')
})
</script>

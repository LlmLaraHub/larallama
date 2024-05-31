<template>
    <h2 class="text-lg font-bold flex justify-start gap-2 items-center text-gray-500 w-full">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
        </svg>

        <span>This is the context used to answer your question.</span>

        <span class="flex justify-end gap-2 items-center w-1/2">
            <Clipboard :content="allPrompt">
                Copy
            </Clipboard>
        </span>
    </h2>
    <div class="text-gray-600 mb-5 mt-2 text-sm">
        The system will query the database based on your question. It will use the results to then confine the LLM to the context of the data that seems most relavent to your question.
    </div>
    <div  class="overflow-auto prose">
        <div v-if="message.prompt_histories.length === 0">
            No history to show on this message.
        </div>
        <div v-else v-for="prompt in message.prompt_histories">
            <div v-html="prompt.prompt"></div>
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

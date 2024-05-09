<template>
    <h2 class="text-lg font-bold">This is the context used to answer your question.</h2>
    <div>
        <div v-if="message.prompt_histories.length !== 0">
            No history to show on this message.
        </div>
        <div v-else v-for="prompt in message.prompt_histories">
            <div v-html="prompt.prompt"></div>
        </div>
    </div>
</template>
<script setup>
const props = defineProps({
    message: Object
})
</script>
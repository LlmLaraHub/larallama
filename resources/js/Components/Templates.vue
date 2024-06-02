<script setup>

const props = defineProps({
    prompts: Object
})

const emit = defineEmits(['choosePrompt'])

const choosePrompt = (prompt) => {
    emit('choosePrompt', prompt);
}

</script>

<template>
    <div class="w-1/3 flex flex-col gap-3  shadow-lg m-2 p-2">
        <h2 class="text-gray-700 text-lg flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
            </svg>

            Choose from a template below to get your started</h2>
        <div v-for="(prompt, label) in prompts" class="border border-gray-300 p-2 rounded-md">
            <div class="font-bold bg-gray-100 p-2">Type: <span class="uppercase">{{label}}</span></div>
            <div class="h-[200px] overflow-y-scroll">
                {{ prompt }}
            </div>
            <div class="flex justify-end mt-2 bg-gray-100 p-2">
                <button type="button" class="btn btn-sm btn-primary" @click="choosePrompt(prompt)">Use</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

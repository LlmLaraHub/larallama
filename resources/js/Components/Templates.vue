<script setup>
import {useToast} from "vue-toastification";
import AvailablePrompts from "@/Components/AvailablePrompts.vue";

const toast = useToast();

const props = defineProps({
    prompts: Object
})

const emit = defineEmits(['choosePrompt'])

const randomAlert = [
    "Good Choice",
    "Using Prompt!",
    "Nice Prompt!",
    "Awesome Prompt!",
    "Great Prompt!",
    "Fantastic Prompt!",
    "Amazing Prompt!",
    "Super Prompt!",
    "Best Prompt!",
    "Wonderful Prompt!",
    "Impressive Prompt!",
    "Fantastic Prompt!",
    "Awesome Prompt!",
    "Great Prompt!",
];

const getRandomAlert = () => {
    return randomAlert[Math.floor(Math.random() * randomAlert.length)];
}

const choosePrompt = (prompt) => {
    toast.info(getRandomAlert, {position: "bottom-right"})
    emit('choosePrompt', prompt);
}

</script>

<template>
    <div class="w-1/3 flex flex-col gap-3 shadow-lgx p-2 border-secondary border ml-2">
        <h2 class="text-lg flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
            </svg>
            Choose from a Prompt Templates below to get your started
        </h2>
        <div class="p-2 rounded-sm bg-warning mb-2 mt-2 text-neutral">
            <div class="text-sm">
                Your prompt can have "tokens" that will be replaced by the system like a classic <a class="link" href="https://mailtrap.io/blog/mail-merge-explained/" target="_blank">Mail Merge</a>
                <p><span class="font-bold">The ones you can use are:</span></p>
            </div>
            <AvailablePrompts/>
        </div>
        <div v-for="(prompt, label) in prompts" class="border border-neutral p-2 rounded-md">
            <div class="font-bold p-2">Type: <span class="uppercase">{{label}}</span></div>
            <div class="h-[200px] overflow-y-scroll">
                {{ prompt }}
            </div>
            <div class="flex justify-end mt-2 p-2">
                <button type="button" class="btn btn-sm btn-ghost rounded-none" @click="choosePrompt(prompt)">Use</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

<script setup>
import {useForm, usePage} from "@inertiajs/vue3";
import SampleQuestions from "./SampleQuestions.vue";
import { ChevronDoubleDownIcon, ChevronRightIcon} from "@heroicons/vue/20/solid";
import {computed, inject, onUnmounted, ref, watch} from "vue";

const props = defineProps({
    loading: {
        type: Boolean,
        default: true
    },
    assistant: Object,
    chat: Object,
})

const emits = defineEmits(['chatSubmitted'])

const errors = ref({})

const form = useForm({
    input: ""
})

const getting_results = ref(false)


let echoChannel = ref(null); // keep track of the Echo channel

watch(() => props.chat?.id, (newVal, oldVal) => {
    if (newVal !== undefined && newVal !== oldVal) { // check if the id has a value and it's different from the previous one
        if(props.chat?.id) {
            echoChannel.value = Echo.private(`chat.user.${usePage().props.user.id}`)
                .listen('.complete', (event) => {
                    getting_results.value = false;
                });
        } else {
            console.log("No chat id yet")
        }

    }
}, { immediate: true }); // { immediate: true } ensures that the watcher checks the initial value

onUnmounted(() => {
    if(echoChannel.value) {
        echoChannel.value.stopListening('.stream'); // stop listening when component is unmounted
    }
});

const starterQuestions = computed(() => {
    if(!props.chat?.id) {
        return usePage().props.assistants.default.starter_questions;
    }

    return usePage().props.assistants[props.chat.assistant.assistant_type]['starter_questions'];
})


const save = () => {
    getting_results.value = true
    form.post(route('assistants.converse', {
        assistant: props.assistant.id
    }), {
        onSuccess: (data) => {
            console.log(data)
            emits('chatSubmitted', form.input)
            form.reset();
        },
        onError: (error) => {
            console.log("Error")
            console.log(error)
            errors.value = error
        }
    })
    
}

const setQuestion = (question) => {
    form.input = question;
}


</script>

<template>
<div>
    <div class="w-full bg-gray-50">
        <form @submit.prevent="save"  autocomplete="off"
              class="relative p-4 flex max-container mx-auto w-full" v-auto-animate>
            <div v-if="errors?.input">
                <div class="text-red-500 text-xs italic">{{ errors }}</div>
            </div>

            <input
                :disabled="loading || getting_results"
                type="text"
                autofocus="true"
                class="caret caret-rose-400 caret-opacity-50
                disabled:opacity-40
                bg-transparent block w-full border-0 py-1.5 ring-0 ring-inset
                ring-rose-500 placeholder:text-gray-400 focus:ring-2
                focus:ring-rose-500 sm:text-sm sm:leading-6"
                v-model="form.input" placeholder=""/>
            <span
                v-if="loading"
                class="loading loading-spinner loading-md"></span>

            <button
                v-else
                :disabled="getting_results" type="submit"
                class="
                    flex justify-start gap-3 items-center
                    bg-gray-850 hover:text-gray-400 text-gray-500 px-2.5 rounded-r-md">
                <span
                    v-if="getting_results"
                    class="loading loading-dots loading-md"></span>
                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"
                     fill="currentColor">
                    <path
                        d="M16.1 260.2c-22.6 12.9-20.5 47.3 3.6 57.3L160 376V479.3c0 18.1 14.6 32.7 32.7 32.7c9.7 0 18.9-4.3 25.1-11.8l62-74.3 123.9 51.6c18.9 7.9 40.8-4.5 43.9-24.7l64-416c1.9-12.1-3.4-24.3-13.5-31.2s-23.3-7.5-34-1.4l-448 256zm52.1 25.5L409.7 90.6 190.1 336l1.2 1L68.2 285.7zM403.3 425.4L236.7 355.9 450.8 116.6 403.3 425.4z"/>
                </svg>
            </button>
        </form>

    </div>
        <div class="mt-2">
            <SampleQuestions :questions="starterQuestions" @chosenQuestion="setQuestion"></SampleQuestions>
        </div>
</div>
</template>

<style scoped>

</style>

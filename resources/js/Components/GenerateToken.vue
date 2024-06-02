<script setup>
import {ref} from "vue";

const props = defineProps({
    size: {
        type: Number,
        default: 32
    }
})

const emit = defineEmits(['generatedToken'])

const token = ref('');
const generateToken = () => {
    token.value = '';

    for(let i=0; i < props.size; i++){
        token.value += Math.random().toString(36).substring(2);
    }

    token.value = token.value.substr(0, props.size);

    emit('generatedToken', token.value);
}

</script>

<template>
    <button class="link text-gray-500 flex gap-2 justify-start mt-2" type="button" @click="generateToken()">
        <Transition
            enter-active-class="duration-50 ease-out"
            enter-from-class="transform opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-50 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="transform opacity-0"
        >
            <svg v-if="token?.length === 0"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </Transition>
        <span>generate token</span>
    </button>
</template>

<style scoped>

</style>

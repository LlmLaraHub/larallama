<template>
    <button @click="scrollToBottom"

            class="fixed top-4 right-4 btn btn-secondary btn-circle text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
        </svg>

    </button>
</template>

<script setup>
import { ref } from 'vue'

const bottomTarget = ref(null)

const scrollToBottom = () => {
    bottomTarget.value?.scrollIntoView({ behavior: 'smooth' })
}

defineExpose({ bottomTarget })
</script>

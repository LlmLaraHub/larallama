<template>
    <div class="flex w-full mt-2 space-x-3 max-w-lg">
            <div> <Initials :initials="message.initials"/></div>
            <div>
                <div class="bg-gray-300 p-3 rounded-r-lg rounded-bl-lg">
                    <p class="text-sm">{{ message.content }}</p>
                </div>
                <span class="text-xs text-gray-500 leading-none">{{ message.created_at_formatted }}</span>
            </div>
    </div>
</template>

<script setup>
import Initials from "./Initials.vue"
const props = defineProps({
    message: String
})
</script>

<style scoped>

</style>

<template>
    <div class="flex w-full mt-2 space-x-3 max-w-lg ml-auto justify-end">
        <div>
            <div class="bg-blue-600 text-white p-3 rounded-l-lg rounded-br-lg">
                <p class="text-md">{{ message.content }}</p>

                <div class="flex justify-end">
                    <Clipboard :content="message.content"/>
                </div>

            </div>
            <div class="flex justify-end mt-2 items-center">
                <span class="text-xs text-gray-500 leading-none">{{message.created_at_formatted}}</span>
            </div>
        </div>
        <img class="rounded-full h-8 w-8" src="/images/writer_small.jpeg">
    </div>
</template>

<script setup>
import Clipboard from "@/Components/Clipboard.vue"
const props = defineProps({
    message: String
})
</script>

<style scoped>

</style>

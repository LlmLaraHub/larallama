<script setup>

const props = defineProps({
    primary: Boolean
})
</script>

<template>
    <div class="flex-shrink-0 w-[2px] h-auto rounded shadow my-4"
         :class="primary ? 'bg-rose-500 shadow-pink-400' : 'bg-gray-300 shadow-gray-700'">
        <slot></slot>
    </div>
</template>

<style scoped>

</style>

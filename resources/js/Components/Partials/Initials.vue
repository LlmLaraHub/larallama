<template>
<span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-500">
    <span class="text-sm font-medium leading-none text-white">{{ initials }}</span>
</span>
</template>

<script setup>
const props = defineProps({
    initials: String
})
</script>

<style scoped>

</style>

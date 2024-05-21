<script setup>

const props = defineProps({
    output: Object
})

</script>

<template>
    <div class="text-xs">
        Active: <span class="font-bold text-gray-600">{{ output.active }}</span>
    </div>
    <div class="text-xs">
        Recurring: <span class="font-bold text-gray-600">{{ output.recurring }}</span>
    </div>
    <div class="text-xs">
        Last Run: <span class="font-bold text-gray-600">{{ output.last_run }}</span>
    </div>
</template>

<style scoped>

</style>

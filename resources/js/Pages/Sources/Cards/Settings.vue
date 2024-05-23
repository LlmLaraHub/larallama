<script setup>

const props = defineProps({
    source: Object
})
</script>

<template>
    <div class="text-xs">
        Type: <span class="font-bold text-gray-600">{{ source.type }}</span>
    </div>
    <div class="text-xs">
        Details: <span class="font-bold text-gray-600">{{ source.details }}</span>
    </div>
    <div class="text-xs">
        Active: <span class="font-bold text-gray-600">{{ source.active }}</span>
    </div>
    <div class="text-xs">
        Recurring: <span class="font-bold text-gray-600">{{ source.recurring }}</span>
    </div>
    <div class="text-xs">
        Last Run: <span class="font-bold text-gray-600">{{ source.last_run }}</span>
    </div>
</template>

<style scoped>

</style>

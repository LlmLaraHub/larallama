<script setup>

const props = defineProps({
    output: Object
})

</script>

<template>
    <h2 class="card-title">{{ output.title }} <span class="text-sm">#{{output.id}}</span></h2>

    <div class="overflow-hidden">
        <span class="font-bold text-xs" v-html="output.summary_truncated"></span>
    </div>

    <div class="text-xs">
        Active: <span class="font-bold">{{ output.active }}</span>
    </div>
    <div class="text-xs">
        Recurring: <span class="font-bold">{{ output.recurring }}</span>
    </div>
    <div class="text-xs">
        Last Run: <span class="font-bold">{{ output.last_run }}</span>
    </div>
</template>

<style scoped>

</style>

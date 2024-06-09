<script setup>

const props = defineProps({
    source: Object
})
</script>

<template>
    <h2 class="card-title">{{ source.title }} <span class="text-sm">#{{source.id}}</span></h2>

    <div class="text-xs">
        Type: <span class="font-bold">{{ source.type }}</span>
    </div>
    <div class="text-xs w-full truncate ellipsis overflow-hidden whitespace-pre-wrap h-24">Details: <span class="font-bold">{{ source.details }}</span>
    </div>
    <div class="text-xs">
        Active: <span class="font-bold">{{ source.active }}</span>
    </div>
    <div class="text-xs">
        Recurring: <span class="font-bold">{{ source.recurring }}</span>
    </div>
    <div class="text-xs">
        Last Run: <span class="font-bold">{{ source.last_run }}</span>
    </div>
</template>

<style scoped>

</style>

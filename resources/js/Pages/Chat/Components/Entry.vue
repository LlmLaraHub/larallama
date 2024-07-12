<script setup>
const props = defineProps({
    entry: Object
})
</script>

<template>
<div>
    <h3 class="font-bold">Entry ID: {{ entry.id }}</h3>

    <div class="prose" v-html="entry.content_formatted"></div>

</div>
</template>

<style scoped>

</style>

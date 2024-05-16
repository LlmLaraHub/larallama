<script setup>

const props = defineProps(
    {
        documentIds: Array
    }
)
</script>

<template>
<button class="btn btn-neutral">
    Delete {{documentIds.length}} Documents
</button>
</template>

<style scoped>

</style>

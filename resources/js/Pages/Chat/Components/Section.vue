<script setup>
import Entry from "@/Pages/Chat/Components/Entry.vue";

const props = defineProps({
    section: Object
})
</script>

<template>
<div>
    <h3 class="font-bold">Section ID: {{ section.id }} - {{ section.subject }}</h3>

    <div class="prose" v-html="section.content"></div>
    <div class="flex justify-end gap-2 items-center text-sm text-gray-500">
        <div> sort: {{ section.sort_order + 1 }}</div>
    </div>
    <div>
        <div v-for="entry in section.entries" class="border border-neutral rounded-md p-4 mt-4">
            <Entry :entry="entry"></Entry>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>

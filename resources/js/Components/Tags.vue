<template>
    <div class="flex flex-wrap justify-center items-center mx-auto gap-2  text-gray-500">
        <div v-for="tag in document.tags" :key="tag.id">
            <span class="badge badge-outline text-xs">{{ tag.name }}</span>
        </div>
    </div>
</template>

<script setup>

const props = defineProps({
    document: Object
});

</script>
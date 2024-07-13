<script setup>
import {ref} from "vue";

const props = defineProps({
    entry: Object
})

const open = ref(false);

</script>

<template>
    <div class="collapse bg-base-200 rounded-none">
        <input type="checkbox" v-model="open" :checked="open"/>
        <div class="collapse-title text-xl font-medium" @click="open = !open">Answer: #{{ entry.id }} {{ entry.title }}</div>
        <div class="collapse-content">
            <div class="prose" v-html="entry.content_formatted"></div>
        </div>
    </div>
</template>

<style scoped>

</style>

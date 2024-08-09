<script setup>
import Entry from "@/Pages/Chat/Components/Entry.vue";
import {ref} from "vue";

const props = defineProps({
    section: Object
})

const activeTab = ref('content');

const toggleTab = (tab) => {
    activeTab.value = tab;
}

</script>

<template>
<div>
    <h3 class="font-bold">#: {{ section.id }} - {{ section.subject }}</h3>

    <div role="tablist" class="tabs tabs-bordered mt-4 mb-2">
        <a role="tab" class="tab"
        :class="{ 'tab-active': activeTab === 'content' }"
            @click="toggleTab('content')"
        >Results</a>
        <a role="tab" class="tab"
        :class="{ 'tab-active': activeTab === 'document' }"
            @click="toggleTab('document')"
        >Document</a>
        <a role="tab" class="tab"
           :class="{ 'tab-active': activeTab === 'prompt' }"
           @click="toggleTab('prompt')"
        >Prompt</a>
    </div>
    <div v-if="activeTab === 'content'">
        <div class="prose" v-html="section.content_formatted"></div>
        <div class="flex justify-end gap-2 items-center text-sm text-gray-500">
            <div> sort: {{ section.sort_order + 1 }}</div>
        </div>
        <div>
            <div v-for="entry in section.entries" class="border border-neutral rounded-md p-4 mt-4">
                <Entry :entry="entry"></Entry>
            </div>
        </div>
    </div>
    <div v-if="activeTab === 'document'">
        <h2 class="font-bold mb-2">
            Related Document: {{ section.document.file_path }}
        </h2>
        <div class="prose" v-html="section.document.original_content"></div>
    </div>
    <div v-if="activeTab === 'prompt'">
        <div class="prose" v-html="section.prompt"></div>
    </div>
</div>
</template>

<style scoped>

</style>

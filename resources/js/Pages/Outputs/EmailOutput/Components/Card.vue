<script setup>

import {Link} from "@inertiajs/vue3";

const props = defineProps({
    output: Object
})
</script>

<template>
    <div class="card rounded-none w-96 bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-gray-600">{{ output.title }}</h2>

            <div class="overflow-hidden">
                <span class="font-bold text-gray-600 text-xs" v-html="output.summary_truncated"></span>
            </div>

            <div class="card-actions justify-between flex items-center">
                <span class="badge badge-default">{{ output.type_formatted}}</span>
                <div class="flex justify-end gap-2 items-center">
                    <Link :href="route('collections.outputs.web_page.edit', {
                                        collection: output.collection_id,
                                        output: output.id
                                    })" class="btn btn-primary rounded-none">Edit</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

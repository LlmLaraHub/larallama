<script setup>

import {Link, useForm} from "@inertiajs/vue3";

const props = defineProps({
    source: Object
})

const form = useForm({})

const run = (source) => {
    form.post(route('collections.sources.run', {
        source: source.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Source is running');
        }
    });
}

</script>

<template>
    <div class="card rounded-none w-96 bg-base-100 shadow-xl" :key="source.id">
        <div class="card-body">
            <h2 class="card-title text-gray-600">{{ source.title }}</h2>
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
            <div class="card-actions justify-end">
                <button @click="run(source)" type="button" class="btn btn-primary rounded-none">Run</button>
                <Link :href="route('collections.sources.websearch.edit', {
                                    collection: source.collection_id,
                                    source: source.id
                                })" class="btn btn-primary rounded-none">Edit</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

<template>
    <div class="shadow-inner-top navbar bg-gray-300 text-neutral-content flex justify-center gap-4">
        <Link
        :class="{'active btn btn-sm' : route().current('collections.index')}"
        :href="route('collections.index')"
        >Collections</Link>
        <Link
        :class="{'active btn btn-sm' : route().current('collections.show')}"
        :href="route('collections.show', {
            collection: collection.id
        })"
        >Collection</Link>
        <Link
        :class="{'active btn btn-sm' : route().current('collections.sources.index')}"
        :href="route('collections.sources.index', {
            collection: collection.id
        })"
        >Sources</Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    collection: Object
})

</script>
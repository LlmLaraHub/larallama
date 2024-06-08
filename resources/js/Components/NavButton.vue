<script setup>

import {Link} from "@inertiajs/vue3";


const props = defineProps({
    href: String,
    name: String,
});

</script>

<template>

    <Link
        :href="href"
        class="text-neutral btn btn-sm"
        :class="{ 'active btn-accent': route().current(name) }">
        <slot></slot>
    </Link>
</template>

<style scoped>

</style>

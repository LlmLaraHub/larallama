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
        class="btn btn-sm rounded-none"
        :class="{ 'active btn-secondary': route().current(name) }">
        <slot></slot>
    </Link>
</template>

<style scoped>

</style>

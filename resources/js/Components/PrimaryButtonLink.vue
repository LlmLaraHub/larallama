<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    href: {
        type: String,
        default: "#",
    },
});
</script>

<template>
    <Link :href="href"
            class="btn btn-primary  rounded-none">
        <slot />
    </Link>
</template>

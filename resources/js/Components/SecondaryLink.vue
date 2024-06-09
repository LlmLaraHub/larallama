<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    type: {
        type: String,
        default: 'inertia',
    },
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
    v-if="type === 'inertia'"
    :href="href"
    class="btn btn-secondary  rounded-none">
        <slot />
    </Link>
</template>

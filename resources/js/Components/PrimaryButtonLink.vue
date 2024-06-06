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
    <Link :href="href" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-none font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </Link>
</template>

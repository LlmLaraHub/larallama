<template>
    <div class="shrink-0 flex">
        <Link :href="route('home')" class="h-12 flex">
            <ApplicationMark class="block w-auto" />
        </Link>
    </div>
</template>

<script setup>
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { Link, usePage } from '@inertiajs/vue3';
</script>

<style scoped>

</style>

<script setup>
import { usePage } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue"
import AppLayout from "@/Layouts/AppLayout.vue"

</script>

<template>
    <GuestLayout v-if="usePage().props.auth.user === null"><slot></slot></GuestLayout>
    <AppLayout v-else><slot></slot></AppLayout>

</template>
<template>
    <div class="shadow-inner-top navbar flex justify-center gap-4 shadow shadow-bottom">
        <NavButton
                   name="collections.show"
            :href="route('collections.show', {
            collection: collection.id
        })"
        >Documents</NavButton>

        <NavButton
            name="collections.events.index"
            :href="route('collections.events.index', {
            collection: collection.id
        })"
        >Events</NavButton>
    </div>

</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CreateChat from './CreateChat.vue';
import NavLink from "@/Components/NavLink.vue";
import NavButton from "@/Components/NavButton.vue";
const props = defineProps({
    collection: Object,
})

</script>

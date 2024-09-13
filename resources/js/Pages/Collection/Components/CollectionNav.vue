<template>
    <div class="shadow-inner-top navbar flex justify-center gap-4 shadow shadow-bottom">
        <NavButton
                   name="collections.show"
            :href="route('collections.show', {
            collection: collection.id
        })"
        >Documents</NavButton>

        <a
            name="`filament.admin.resources.events.index"
            :href="route('filament.admin.resources.events.index', {
                tableFilters: {
                    collection_id: {
                        value: collection.id
                    }
                }
            })"
        >Events</a>
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

<script setup>

import {useToast} from "vue-toastification";
import {computed} from "vue";
import {usePage} from "@inertiajs/vue3";
import DisplayMenu from "@/Components/DisplayMenu.vue";

const toast = useToast();

const emits = defineEmits(['filter'])
const props = defineProps({
    collection: Object,
})

const filters = computed(() => {
    return usePage().props.filters?.data;
})

const getFilter = (filter) => {
    emits('filter', filter)
}

</script>

<template>
    <DisplayMenu :items="filters" @itemSelected="getFilter">
        <template #title>
            Filters
        </template>
    </DisplayMenu>
</template>

<style scoped>

</style>

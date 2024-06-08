<script setup>

import {useToast} from "vue-toastification";
import {computed} from "vue";
import {usePage} from "@inertiajs/vue3";

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
    <details class="dropdown dropdown-top">
        <summary class="m-1 btn btn-neutral">Filters</summary>
        <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-48 ">
            <li v-for="filter in filters" :key="filter.id">
                <button type="button" @click="getFilter(filter)">{{ filter.name }}</button>
            </li>
            <li><button type="button" @click="getFilter({})">Reset</button></li>
        </ul>
    </details>
</template>

<style scoped>

</style>

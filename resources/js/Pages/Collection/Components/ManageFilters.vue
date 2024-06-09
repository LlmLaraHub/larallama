<script setup>

import {useToast} from "vue-toastification";
import {computed} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";

const toast = useToast();

const emits = defineEmits(['filter'])
const props = defineProps({
    collection: Object,
})

const filters = computed(() => {
    return usePage().props.filters?.data;
})

const form = useForm({})

const deleteFilter = (filter) => {
    form.delete(route("filters.delete", {
        filter: filter.id
    }), {
        preserveScroll: true,
        onSuccess: params => {
            toast("Deleted")
        }
    })
}

</script>

<template>
    <details class="dropdown">
        <summary class="m-1 btn btn-neutral">Filters</summary>
        <ul class="dark:bg-neutral dropdown-content z-[1] shadow-lg rounded-none w-72 -ml-36 pb-2 pr-2">
            <li
                v-if="filters?.length > 0"
                v-for="filter in filters" :key="filter.id" class="h-12 ml-2 mt-2">
                    <button type="button" class="btn rounded-none w-full justify-between" @click="deleteFilter(filter)">
                        <span>{{ filter.name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
            </li>
            <li v-else>No Filters. Select documents to create</li>
        </ul>
    </details>
</template>

<style scoped>

</style>

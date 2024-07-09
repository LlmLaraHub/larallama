<script setup>
import {computed, ref} from "vue";


const searchTerm = ref("");

const emits = defineEmits(['itemSelected']);

const openMenu = ref(null);

const props = defineProps({
    items: Object,
    search: {
        type: Boolean,
        default: false
    },
});

const resetSearch = () => {
    searchTerm.value = "";
}

const chooseItem = (item) => {
    emits('itemSelected', item)
    openMenu.value.removeAttribute('open')
}

const filteredItems = computed(() => {
    if (searchTerm.value.length === 0) {
        return props.items;
    }
    return props.items.filter(item => item.name.toLowerCase().includes(searchTerm.value.toLowerCase()));
})

</script>

<template>
    <details ref="openMenu" class="dropdown dropdown-top">
        <summary class="m-1 btn btn-neutral btn-outline">
            <slot name="title"></slot>
        </summary>
        <ul class="
        border border-neutral
        p-2 shadow menu dropdown-content z-[1]
         bg-base-100 rounded-box w-48 ">
            <li v-if="filteredItems.length === 0" class="flex-col mx-auto justify-center text-center">
                <span class="text-sm">Nothing here to see!</span>
                <button
                    v-if="search"
                    class="link text-sm" type="button" @click="resetSearch">(reset)</button>
            </li>
            <li
                v-if="filteredItems.length > 0"
                v-for="item in filteredItems" :key="item.id">
                <button type="button"
                        @click="chooseItem(item)">{{ item.name }}</button>
            </li>
            <li v-if="filteredItems.length > 0">
                <input v-if="search"
                       v-model="searchTerm" type="text"
                       placeholder="Search..."
                       class="input input-bordered w-full rounded-none">
            </li>
        </ul>
    </details>
</template>

<style scoped>

</style>

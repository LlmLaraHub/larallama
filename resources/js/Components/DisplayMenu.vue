<script setup>
import {ref} from "vue";

const emits = defineEmits(['itemSelected']);

const openMenu = ref(null);

const props = defineProps({
    items: Object,
});

const chooseItem = (item) => {
    emits('itemSelected', item)
    openMenu.value.removeAttribute('open')
}


</script>

<template>
    <details ref="openMenu" class="dropdown dropdown-top">
        <summary class="m-1 btn btn-neutral">
            <slot name="title"></slot>
        </summary>
        <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-48 ">
            <li v-for="item in items" :key="item.id">
                <button type="button" @click="chooseItem(item)">{{ item.name }}</button>
            </li>
        </ul>
    </details>
</template>

<style scoped>

</style>

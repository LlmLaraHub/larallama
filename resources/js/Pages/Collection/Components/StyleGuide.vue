<script setup>

import {useToast} from "vue-toastification";
import {computed, ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import DisplayMenu from "@/Components/DisplayMenu.vue";

const toast = useToast();

const emits = defineEmits(['style-guide-persona', 'persona', 'audience', 'style-guide-audience'])

const props = defineProps({
    collection: Object,
})

const audiences = computed(() => {
    return usePage().props.audiences?.data;
})

const personas = computed(() => {
    return usePage().props.personas?.data;
})

const getPersona = (filter) => {
    emits('persona', filter)
}

const getAudience = (filter) => {
    emits('audience', filter)
}

</script>

<template>
    <DisplayMenu :items="personas" @itemSelected="getPersona">
        <template #title>
            Personas
        </template>
    </DisplayMenu>

<!--    how do I remove the open attribute from details after they click li -->

    <DisplayMenu :items="audiences" @itemSelected="getAudience">
            <template #title>
                Audiences
            </template>
    </DisplayMenu>
</template>

<style scoped>

</style>

<script setup>

import {useToast} from "vue-toastification";
import {computed, ref} from "vue";
import {usePage} from "@inertiajs/vue3";

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

const openAudience = ref(null);

const getAudience = (filter) => {
    emits('audience', filter)
    openAudience.value.removeAttribute('open')
}

</script>

<template>
    <details class="dropdown dropdown-top">
        <summary class="m-1 btn btn-neutral">
            Personas
        </summary>
        <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-48 ">
            <li v-for="persona in personas" :key="persona.id">
                <button type="button" @click="getPersona(persona)">{{ persona.name }}</button>
            </li>
            <li><button type="button" @click="getPersona({})">Reset</button></li>
        </ul>
    </details>

<!--    how do I remove the open attribute from details after they click li -->
    <details ref="openAudience" class="dropdown dropdown-top">
        <summary class="m-1 btn btn-neutral">
            Audiences
        </summary>
        <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-48 ">
            <li v-for="audience in audiences" :key="audience.id">
                <button type="button" @click="getAudience(audience)">{{ audience.name }}</button>
            </li>
        </ul>
    </details>

</template>

<style scoped>

</style>

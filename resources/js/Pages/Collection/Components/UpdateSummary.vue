<script setup>


import {router, useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import {ref} from "vue";
import {usePage} from "@inertiajs/vue3";

const toast = useToast();

const props = defineProps({
    document: Object,
});

const emits = defineEmits(['updateSummary', 'startingUpdate']);

const form = useForm({});

const running = ref(false);
const updateSummary = () => {
    emits('startingUpdate');
    running.value = true;
    axios.post(route('collections.documents.update-summary', {
        document: props.document.id
    })).then((results) => {
        console.log("Results", results);
        toast.success('Done!', {
            position: "bottom-right",
        });
        running.value = false;
        emits('updateSummary', results.data);
    }).catch((results) => {
        toast.error('Error updating document summary', {
            position: "bottom-right",
        });
        running.value = false;
    });
}
</script>

<template>
    <button
        :disabled="running"
        type="button"
        class="btn btn-primary rounded-none"
        @click="updateSummary">
        Generate Summary
    </button>
</template>

<style scoped>

</style>

<script setup>

import {useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";

const toast = useToast()

const props = defineProps(
    {
        documentIds: Array
    }
)

const form = useForm({
    documents: props.documentIds
})

const deleteDocs = () => {
    toast.info("Deleting documents")
    form.delete(route('documents.delete'), {
        preserveScroll: true
    });
}
</script>

<template>
<button @click="deleteDocs" class="btn btn-neutral">
    Delete {{documentIds.length}} Documents
</button>
</template>

<style scoped>

</style>

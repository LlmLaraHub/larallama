<script setup>

import {useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import {ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const toast = useToast()

const emit = defineEmits(['deleted'])

const props = defineProps(
    {
        documentIds: Array
    }
)

const form = useForm({
    documents: []
})

const deleteDocs = () => {
    toast.info("Deleting documents")
    form
        .transform((data) => ({
            ...data,
            documents: props.documentIds
        }))
        .delete(route('documents.delete'), {
        preserveScroll: true,
        onSuccess: params => {
            form.reset();
            showConfirm.value = false;
            emit('deleted')
        }
    });
}

const showConfirm = ref(false)

const confirm = () => {
    showConfirm.value = true;
}
</script>

<template>
<button @click="confirm" class="btn btn-neutral">
    Delete {{documentIds.length}} Documents
</button>

    <ConfirmationModal :show="showConfirm" @close="showConfirm = false">
        <template #title>
            Delete Documents
        </template>

        <template #content>
           You will be deleting {{documentIds.length}} document<span v-if="documentIds.length > 1">s</span>
        </template>

        <template #footer>
            <SecondaryButton @click.native="showConfirm = false">
                Nevermind
            </SecondaryButton>

            <DangerButton class="ml-2" @click.native="deleteDocs" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Delete Documents
            </DangerButton>
        </template>
    </ConfirmationModal>
</template>

<style scoped>

</style>

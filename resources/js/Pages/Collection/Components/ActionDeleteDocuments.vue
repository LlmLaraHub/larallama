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
        collection: Object,
        documentIds: Array
    }
)

const form = useForm({
    documents: []
})

const deleteAllForm = useForm({})

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


const deleteAll = () => {
    toast.info("Deleting All Documents")
    deleteAllForm
        .delete(route('documents.delete_all', {
            collection: props.collection.id
        }), {
            preserveScroll: true,
            onSuccess: params => {
                form.reset();
                showConfirm.value = false;
                emit('deletedAll')
            }
        });
}

const showConfirm = ref(false)
const showConfirmAll = ref(false)

const confirm = () => {
    showConfirm.value = true;
}

const confirmAll = () => {
    showConfirmAll.value = true;
}
</script>

<template>
<button @click="confirm" class="btn btn-warning ">
    Delete {{documentIds.length}} Documents
</button>
<button @click="confirmAll" class="btn btn-error">
    Delete  All Documents
</button>

    <ConfirmationModal :show="showConfirmAll" @close="showConfirmAll = false">
        <template #title>
            Delete All Documents
        </template>

        <template #content>
            This will delete All document for this collection
        </template>

        <template #footer>
            <SecondaryButton @click.native="showConfirmAll = false">
                Nevermind
            </SecondaryButton>

            <DangerButton class="ml-2" @click.native="deleteAll" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Delete All Documents
            </DangerButton>
        </template>
    </ConfirmationModal>

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

            <DangerButton class="ml-2" @click.native="deleteDocs" :class="{ 'opacity-25': deleteAllForm.processing }" :disabled="form.processing">
                Delete Documents
            </DangerButton>
        </template>
    </ConfirmationModal>
</template>

<style scoped>

</style>

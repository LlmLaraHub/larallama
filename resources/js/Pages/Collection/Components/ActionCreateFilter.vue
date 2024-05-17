<script setup>

import {useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import {ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import DialogModal from "@/Components/DialogModal.vue";

const toast = useToast()

const emit = defineEmits(['created'])

const props = defineProps(
    {
        documentIds: Array,
        collection: Object
    },
)

const showModal = ref(false)

const form = useForm({
    documents: [],
    name: "Filter Name",
    description: "Helpful info for the users of the filter"
})

const submit = () => {
    toast.info("Creating Filters")
    form
        .transform((data) => ({
            ...data,
            documents: props.documentIds
        }))
        .post(route('filters.create', {
            collection: props.collection.id
        }), {
        preserveScroll: true,
        onSuccess: params => {
            form.reset();
            emit('created')
        },
        onError: params => {
            toast("Error creating filters :( ")
        }
    });
}


</script>

<template>
<button type="button" @click="showModal = true" class="btn btn-neutral">
    Create Filter for {{documentIds.length}} Documents
</button>

    <DialogModal :show="showModal">
        <template #title>
            Create a filter to use for chatting with documents. It will include the
            {{ documentIds.length }} document(s) you selected.

        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div>
                    <InputLabel>Name</InputLabel>
                    <TextInput
                        class="w-full"
                        type="text" v-model="form.name"></TextInput>
                    <InputError v-if="form.errors.name"/>
                </div>


                <div>
                    <InputLabel>Description</InputLabel>
                    <TextInput
                        class="w-full"
                        type="text" v-model="form.description"></TextInput>
                    <InputError v-if="form.errors.description"/>
                </div>

                <div class="flex mt-2 justify-end gap-2">
                    <button type="submit" class="btn btn-neutral">
                        Save Filter
                    </button>
                    <button type="button" @click="showModal = false" class="btn btn-ghost">
                        Cancel
                    </button>
                </div>
            </form>
        </template>
    </DialogModal>


</template>

<style scoped>

</style>

<template>
<AppLayout title="Source Web File">
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ details.name }}
        </h2>
        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormWrapper @submitted="submit">
                    <div>
                        <div class="max-w-7xl mx-auto sm:py-10 sm:px-6 lg:px-8">
                            <FormSection>
                                <template #title>
                                    {{ details.name }}
                                </template>
                                <template #description>
                                    {{ details.description }}
                                </template>

                                <template #form>
                                   <ResourceForm v-model="form"/>
                                </template>

                                <template #actions>
                                    <PrimaryButton @click="submit">Save</PrimaryButton>
                                </template>

                            </FormSection>
                        </div>
                    </div>
                </FormWrapper>
            </div>
        </div>
    </template>
</AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import ResourceForm from "./Partials/ResourceForm.vue";
import FormWrapper from "@/Components/FormWrapper.vue";
import FormSection from "@/Components/TypeFormSection.vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Link } from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
const toast = useToast();

const props = defineProps({
    details: Object,
    outbound: Object,
    response_type: Object
})

const form = useForm({
    meta_data: props.response_type.meta_data,
    prompt_token: props.response_type.prompt_token,
})

const submit = () => {
    form
        .transform((data) => ({
            meta_data:  JSON.parse(data.meta_data),
        }))
        .put(route("response_types.[RESOURCE_KEY].update", {
        outbound: props.outbound.id,
        response_type: props.response_type.id
    }), {
        preserveScroll: true,
        onError: params => {
            toast.error("Error saving updates check validation")
        }
    });
}
</script>

<style scoped>

</style>

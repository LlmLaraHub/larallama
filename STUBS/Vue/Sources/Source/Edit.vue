<template>
<AppLayout title="Source Web File">
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            [RESOURCE_NAME]
        </h2>
        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormWrapper @submitted="submit">
                    <div>
                        <div class="max-w-7xl mx-auto sm:py-10 sm:px-6 lg:px-8">
                            <FormSection>
                                <template #title>
                                    {{ details.title }}
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
import FormWrapper from "@/Components/FormWrapper.vue";
import FormSection from "@/Components/FormSection.vue";

import PrimaryButton from '@/Components/PrimaryButton.vue';

import { useForm } from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import ResourceForm from "./Partials/ResourceForm.vue";
const toast = useToast();

const props = defineProps({
    details: Object,
    project: Object,
    source: Object
})

const form = useForm({
    meta_data: props.source.meta_data,
    description: props.source.description,
    name: props.source.name
})

const submit = () => {
    form.put(route("sources.[RESOURCE_KEY].update", {
        project: props.project.id,
        source: props.source.id
    }), {
        preserveScroll: true,
        onError: params => {
            toast.error("Check validation")
        }
    });
}
</script>

<style scoped>

</style>

<template>
    <AppLayout title="Source Web File">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ details.title }}
            </h2>
            <div>
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <FormWrapper>
                        <div>
                            <div class="max-w-7xl mx-auto sm:py-10 sm:px-6 lg:px-8">
                                <FormSection>
                                    <template #title>
                                        {{ details.title }}
                                    </template>
                                    <template #description>
                                        {{ details.description }}
                                    </template>
                                    <template #form_description>
                                        <h3 class="text-xl text-gray-900 mb-4">Response Types</h3>
                                        <div class="mt-2">
                                            Response types allow you to search the database,
                                            communicate with LLMs, create message history, or perform other actions based on the incoming question before replying to the user.
                                            Attach and sort the ones you would like to use.
                                        </div>
                                    </template>
                                    <template #existing>
                                        <ExistingResponseTypes
                                            :outbound="outbound"
                                        />
                                        <div class="text-gray-500">-or-</div>
                                        <CloneOutbound
                                            :currentOutbound="outbound"
                                            :project="project"/>
                                    </template>

                                    <template #form>

                                        <ResponseTypes
                                            :outbound="outbound"
                                            :response_types="response_types"/>
                                    </template>

                                    <template #actions>
                                        <SecondaryButtonLink
                                        :href="route('projects.show', {
                                            project: project.id
                                        })"
                                        >
                                            Back to Project
                                        </SecondaryButtonLink>
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
import FormSection from "@/Components/TypeFormSection.vue";
import CloneOutbound from "@/Pages/Outbounds/Components/CloneOutbound.vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Link } from "@inertiajs/vue3";

import {useToast} from "vue-toastification";
const toast = useToast();
import ResponseTypes from "../Components/ResponseTypes.vue";
import ExistingResponseTypes from "../Components/ExistingResponseTypes.vue";
import SecondaryButtonLink from "@/Components/SecondaryButtonLink.vue";

const props = defineProps({
    details: Object,
    project: Object,
    outbound: Object,
    response_types: Object
})


</script>

<style scoped>

</style>

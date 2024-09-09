<script setup>
import {Link, useForm} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    project: Object,
    statuses: Array
})


const form = useForm({
    name: props.project.data.name,
    start_date: props.project.data.start_date,
    end_date: props.project.data.end_date,
    status: props.project.data.status,
    content: props.project.data.content,
    system_prompt: props.project.data.system_prompt,
    product_or_service: props.project.data.product_or_service,
    target_audience: props.project.data.target_audience,
    budget: props.project.data.budget,
});

const save = () => {
    form.put(route('projects.update', {
        project: props.project.data.id
    }), {
        errorBag: 'saveProject',
        preserveScroll: true,
    });
}


</script>

<template>
<AppLayout title="Projects">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <FormSection @submitted="save">>
        <template #title>
            Project Edit
        </template>

        <template #description>
            You can edit your project here. If it is a full edit then kick off the project
            again to clear out ALL existing tasks and messages.
        </template>

        <template #form>
            <div class="col-span-12 sm:col-span-6">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="col-span-12 sm:col-span-6">
                <InputLabel for="Content" value="System Prompt" />
                <textarea
                    v-model="form.system_prompt"
                    class="
                    text-lg
                    w-full border-gray-300 textarea textarea-bordered"
                    id="content" rows="10">

                </textarea>
                <InputError :message="form.errors.system_prompt" class="mt-2" />
            </div>

            <div class="col-span-12 sm:col-span-6">
                <InputLabel for="Content" value="Content" />
                <textarea
                    v-model="form.content"
                    class="w-full border-gray-300 textarea textarea-bordered" id="content" rows="20">

                </textarea>
                <InputError :message="form.errors.content" class="mt-2" />
            </div>


            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="start_date" value="Start Date" />
                <TextInput
                    id="start_date"
                    v-model="form.start_date"
                    type="date"
                    class="mt-1 block w-full"
                    required
                />
                <InputError :message="form.errors.start_date" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="end_date" value="End Date" />
                <TextInput
                    id="end_date"
                    v-model="form.end_date"
                    type="date"
                    class="mt-1 block w-full"
                    required
                />
                <InputError :message="form.errors.end_date" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="status" value="Status" />
                <select
                    v-model="form.status"
                    class="select select-bordered w-full max-w-xs">
                    <template v-for="(status, value) in statuses">
                        <option :value="status.id">
                            {{ status.name }}
                        </option>
                    </template>
                </select>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>


        </template>

        <template #actions>
            <div class="flex justify-end gap-2">

                <ActionMessage :on="form.recentlySuccessful" class="me-3">
                    Updated.
                </ActionMessage>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Update
                </PrimaryButton>

                <a
                    :href="route('projects.show', {
                    project: project.data.id
                })"
                    class="btn btn-secondary">
                    View
                </a>
            </div>
        </template>

    </FormSection>
    </div>
</AppLayout>

</template>


<style scoped>

</style>

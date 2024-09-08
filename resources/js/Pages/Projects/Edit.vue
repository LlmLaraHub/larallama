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
    campaign: Object,
    productServices: Array,
    statuses: Array,
    content_start: String,
})


const form = useForm({
    name: props.campaign.data.name,
    start_date: props.campaign.data.start_date,
    end_date: props.campaign.data.end_date,
    status: props.campaign.data.status,
    content: props.campaign.data.content,
    product_or_service: props.campaign.data.product_or_service,
    target_audience: props.campaign.data.target_audience,
    budget: props.campaign.data.budget,
});

const save = () => {
    form.put(route('projects.update', {
        campaign: props.campaign.data.id
    }), {
        errorBag: 'saveCampaign',
        preserveScroll: true,
    });
}


</script>

<template>
<AppLayout title="Projects">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <FormSection @submitted="save">>
        <template #title>
            Campaign Create
        </template>

        <template #description>
            Info here about kicking off your campaign....
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
                <InputLabel for="Content" value="Content" />
                <textarea
                    v-model="form.content"
                    class="w-full border-gray-300 textarea textarea-bordered" id="content" rows="20">

                </textarea>
                <InputError :message="form.errors.content" class="mt-2" />
            </div>

            <div class="col-span-12 sm:col-span-6">
                <InputLabel for="target_audience" value="Target Audience" />
                <textarea
                    v-model="form.target_audience"
                    class="w-full border-gray-300 textarea textarea-bordered"
                    id="target_audience" rows="5">

                </textarea>
                <InputError :message="form.errors.target_audience" class="mt-2" />
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
                <InputError :message="form.errors.email" class="mt-2" />
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
                <InputError :message="form.errors.email" class="mt-2" />
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

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="status" value="Product of Service" />
                <select
                    v-model="form.product_or_service"
                    class="select select-bordered w-full max-w-xs">
                    <template v-for="service in productServices" :key="service.id">
                        <option :value="service.id">
                            {{ service.name }}
                        </option>
                    </template>
                </select>
                <InputError :message="form.errors.product_or_service" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="budget" value="Budget" />
                <label class="input input-bordered flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>

                    <input
                        v-model="form.budget"
                        type="text" class="grow border-none" placeholder="2000" />
                </label>
                <InputError :message="form.errors.budget" class="mt-2" />
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
                    campaign: campaign.data.id
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

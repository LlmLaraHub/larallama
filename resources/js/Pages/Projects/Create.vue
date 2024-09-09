<script setup>
import {Link, useForm, usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import {computed} from "vue";

const props = defineProps({
    project: Object,
    statuses: Array,
    content_start: [],
})


const form = useForm({
    name: 'You Project Name',
    start_date: '',
    end_date: '',
    chat_driver: '',
    embedding_driver: '',
    status: 'draft',
    content: "Choose from a Template or write your own this will kick off the project",
    system_prompt: "This will be used at the root of all prompts to guide the LLM about the project",
});

const save = () => {
    form.post(route('projects.store'), {
        errorBag: 'saveProject',
        preserveScroll: true,
    });
}

const addPrompt = (prompt) => {
    form.content = prompt.content;
    form.system_prompt = prompt.system_prompt;
}

const activeLlmsWithEmbeddings = computed(() => {
    return usePage().props.active_llms_with_embeddings;
});


const activeLlms = computed(() => {
    return usePage().props.active_llms;
});

</script>

<template>
<AppLayout title="Projects">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <FormSection @submitted="save">>
        <template #title>
            Project Create
        </template>

        <template #description>
            Info here about kicking off your project


            <div class="mt-2">
                <h2 class="text-secondary text-lg">Templates</h2>
                <div class="mt-2">
                    <ul>
                        <li v-for="content_template in content_start">
                            <button @click="addPrompt(content_template)"
                                  class="btn btn-outline">
                                {{ content_template.key }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
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
                <InputLabel for="Content" value="Prompt" />
                <textarea
                    v-model="form.content"
                    class="
                    text-lg
                    w-full border-gray-300 textarea textarea-bordered"
                    id="content" rows="20">

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

            <div class="col-span-6 sm:col-span-full">
                <InputLabel for="status" value="Status" />
                <select
                    v-model="form.status"
                    class="select select-bordered w-full">
                    <template v-for="(status, value) in statuses">
                        <option :value="status.id">
                            {{ status.name }}
                        </option>
                    </template>
                </select>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="status" value="Chat LLM" />
                <select
                    v-model="form.chat_driver"
                    class="select select-bordered w-full max-w-xs">
                    <template v-for="chat_driver in activeLlms" :key="chat_driver.key">
                        <option :value="chat_driver.key">
                            {{ chat_driver.title }}
                        </option>
                    </template>
                </select>
                <InputError :message="form.errors.chat_driver" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="status" value="Embedding LLM" />
                <select
                    v-model="form.embedding_driver"
                    class="select select-bordered w-full max-w-xs">
                    <template v-for="chat_embedder in activeLlmsWithEmbeddings" :key="chat_embedder.key">
                        <option :value="chat_embedder.key">
                            {{ chat_embedder.title }}
                        </option>
                    </template>
                </select>
                <InputError :message="form.errors.embedding_driver" class="mt-2" />
            </div>

        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>

    </FormSection>
    </div>
</AppLayout>

</template>


<style scoped>

</style>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    setting: Object,
});

const form = useForm({
    _method: 'PUT',
    api_key: props.setting.secrets?.ollama?.api_key,
    api_url: props.setting.secrets?.ollama?.api_url,
});


const updateSecrets = () => {

    form.put(route('settings.update.ollama', {
        setting: props.setting.id,
    }), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
    });
};

</script>

<template>
    <FormSection @submitted="updateSecrets">
        <template #title>
            Add Ollama Token and Url
        </template>

        <template #description>
            This service runs locally but sometimes is hosted
            see the docs
            <a
                class="underline"
                href="https://github.com/ollama/ollama/blob/main/docs/api.md" target="_blank">here</a>
        </template>

        <template #form>


            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Api Token" />
                <TextInput
                    id="name"
                    v-model="form.api_key"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.api_key" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Api Url" />
                <TextInput
                    id="name"
                    v-model="form.api_url"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.api_url" class="mt-2" />
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
</template>

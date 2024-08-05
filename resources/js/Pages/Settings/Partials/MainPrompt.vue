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
import SecretInput from "@/Components/SecretInput.vue";
import TextArea from "@/Components/TextArea.vue";

const props = defineProps({
    setting: Object,
});

const form = useForm({
    _method: 'PUT',
    main_collection_prompt: props.setting.main_collection_prompt,
});


const updateSecrets = () => {

    form.put(route('settings.update.main_collection', {
        setting: props.setting.id,
    }), {
        errorBag: 'updateMainCollection',
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="updateSecrets">
        <template #title>
            Main Collection Prompt
        </template>

        <template #description>
            This will guide the overall chat experience while in the Collection.
            The LLM will use this to help guide it.
        </template>

        <template #form>




            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="organization" value="Main Collection Prompt" />
                <TextArea
                    id="name"
                    v-model="form.main_collection_prompt"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.main_collection_prompt" class="mt-2" />
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

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
import TextArea from "@/Components/TextArea.vue";
import PersonaItem from "@/Pages/StyleGuide/Partials/PersonaItem.vue";

const props = defineProps({
    images: Object,
});




</script>

<template>
    <FormSection @submitted="addStyle">
        <template #title>
            Images
        </template>

        <template #description>
            Here you can save style sets of images.
        </template>
        <template #intro>
            <h2>Existing Image Sets </h2>
            <div>
                <div class="text-2xl mt-10 text-secondary" >
                    Coming Soon!
                </div>
            </div>


        </template>

    </FormSection>
</template>

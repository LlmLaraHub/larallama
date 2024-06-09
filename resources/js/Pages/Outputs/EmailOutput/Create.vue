<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Templates from '@/Components/Templates.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import Generate from "@/Pages/Outputs/WebPage/Components/Generate.vue";

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    recurring: Object,
    prompts: Object,
});

const form = useForm({
    title: 'Daily Email Summary',
    summary: props.prompts.email,
    active: 1,
    recurring: "not",
    meta_data: {
        to: "your@mail.test,bob@email.test"
    },
    to_emails: "",
});

const choosePrompt = (prompt) => {
    form.summary = prompt;
}

const to_emails = ref("")

const submit = () => {
    form.
        transform((data) => ({
            ...data,
            meta_data: {
                to: form.to_emails
            },
        }))
        .post(
        route('collections.outputs.email_output.store', {
            collection: props.collection.data.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            }
        });
}
</script>

<template>
    <AppLayout title="Email Output">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl rounded-none p-5">
                    <Intro></Intro>

                    <form @submit.prevent="submit" class="p-10 ">
                        <div class="flex">
                            <div class="w-3/4 border border-secondary rounded-none p-5">

                                <Resources
                                    :recurring="recurring"
                                    v-model="form">

                                </Resources>


                                <div class="flex justify-end items-center gap-4">
                                    <PrimaryButton type="submit">
                                        Save
                                    </PrimaryButton>
                                    <SecondaryLink :href="route('collections.outputs.index', {
                                collection: collection.data.id

                            })">
                                        Cancel
                                    </SecondaryLink>
                                </div>
                            </div>
                            <Templates
                                @choosePrompt="choosePrompt"
                                :prompts="prompts"/>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import Generate from "@/Pages/Outputs/WebPage/Components/Generate.vue";
import Templates from "@/Components/Templates.vue";

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    prompts: Object,
    recurring: Object
});

const form = useForm({
    title: 'Name of the API',
    summary: 'Choose a prompt from the right or make your own',
    active: 1,
    recurring: "not",
    meta_data: {
        token: ""
    },
    token: "",
});

const token = ref("")

const choosePrompt = (prompt) => {
    form.summary = prompt;
}

const submit = () => {
    form.
        transform((data) => ({
            ...data,
            meta_data: {
                token: form.token
            },
        }))
        .post(
        route('collections.outputs.api_output.store', {
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

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import Templates from "@/Components/Templates.vue";

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    source: {
        type: Object
    },
    recurring: {
        type: Object
    },
    prompts: {
        type: Object
    },
    info: String,
    type: String
});

const form = useForm({
    title: '',
    details: '',
    recurring: 'not',
    secrets: {
        username: "bob@bobsburgers.com",
        password: "password",
        host: "mail.bobsburgers.com",
        email_box: "Inbox",
        port: 993,
        delete: true,
    },
    active: true
});

const choosePrompt = (prompt) => {
    form.details = prompt;
}

const submit = () => {
    form.post(
        route('collections.sources.email_box_source.store', {
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
    <AppLayout title="Sources">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-2 lg:px-2">
                <div class="overflow-hidden shadow-xl rounded-none p-5">
                    <Intro></Intro>

                    <form @submit.prevent="submit" class="p-5">
                        <div class="flex">
                            <div class="w-3/4 border border-secondary p-5">
                                <Resources
                                    :recurring="recurring"
                                    v-model="form">

                                </Resources>


                                <div class="flex justify-end items-center gap-4">
                                    <PrimaryButton type="submit">
                                        Save
                                    </PrimaryButton>
                                    <SecondaryLink :href="route('collections.sources.index', {
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

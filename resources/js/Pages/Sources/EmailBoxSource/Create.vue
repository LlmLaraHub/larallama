<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';

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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ type}}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <Intro></Intro>

                    <form @submit.prevent="submit" class="p-10 ">
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
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

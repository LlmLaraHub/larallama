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

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    }
});

const form = useForm({
    title: '',
    summary: '',
    active: false,
    public: false,
});

const updateSummary = (summary) => {

    form.summary = summary;
}


const submit = () => {
    form.post(
        route('collections.outputs.web_page.store', {
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
                Sources
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <Intro>
                        Web Search Source
                        <template #description>
                            Add a query below and you will be able to run it as a web search.
                            This will add documents to your collection.
                        </template>
                    </Intro>

                    <form @submit.prevent="submit" class="p-10 ">
                        <Resources
                            :collection="collection.data"
                        v-model="form">

                            <Generate :collection="collection.data"
                            @generated="updateSummary"
                            ></Generate>
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

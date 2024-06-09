<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import Delete from "@/Pages/Sources/Components/Delete.vue";
import Templates from "@/Components/Templates.vue";

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    prompts: {
        type: Object
    },
    source: {
        type: Object
    },
    recurring: Object,
    info: String,
    type: String
});

const choosePrompt = (prompt) => {
    form.details = prompt;
}


const form = useForm({
    title: props.source.data.title,
    details: props.source.data.details,
    active: props.source.data.active,
    recurring: props.source.data.recurring
});


const submit = () => {
    form.put(
        route('collections.sources.web_search_source.update', {
            collection: props.collection.data.id,
            source: props.source.data.id
        }), {
            preserveScroll: true,
        });
}
</script>

<template>
    <AppLayout title=" Edit Web Source">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl p-5">
                    <Intro>
                        {{ type }}
                        <template #description>
                            {{ info }}
                        </template>
                    </Intro>
                    <form @submit.prevent="submit" class="p-10 ">
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

                                    <Delete :source="source.data"></Delete>
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

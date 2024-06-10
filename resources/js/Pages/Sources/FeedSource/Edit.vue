<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    source: {
        type: Object
    },
    recurring: Object,
    info: String,
    type: String
});

const form = useForm({
    title: props.source.data.title,
    details: props.source.data.details,
    active: props.source.data.active,
    recurring: props.source.data.recurring,
    meta_data: {
        example: props.source.data.meta_data.example
    }

});


const submit = () => {
    form.put(
        route('collections.sources.feed_source.update', {
            collection: props.collection.data.id,
            source: props.source.data.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Source updated');
            },
            onError: () => {
                toast.error('Error updating source see validation errors or logs');
            }
        });
}
</script>

<template>
    <AppLayout :title="type">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ type}}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <Intro>
                        {{ type }}
                        <template #description>
                            {{ info }}
                        </template>
                    </Intro>

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
                                Back
                            </SecondaryLink>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

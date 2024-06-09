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
import Templates from "@/Components/Templates.vue";
import Delete from "@/Pages/Sources/Components/Delete.vue";
const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    source: {
        type: Object
    },
    prompts: Object,
    recurring: Object,
    info: String,
    type: String
});

const form = useForm({
    title: props.source.data.title,
    details: props.source.data.details,
    active: props.source.data.active,
    recurring: props.source.data.recurring,
    secrets: {
        token: props.source.data.secrets.token,
    },

});


const submit = () => {
    form.put(
        route('collections.sources.webhook_source.update', {
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

const choosePrompt = (prompt) => {
    form.details = prompt;
}

</script>

<template>
    <AppLayout :title="type">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <Intro>
                        {{ type }}
                        <template #description>
                            {{ info }}
                        </template>
                    </Intro>

                    <form @submit.prevent="submit" class="p-10 ">
                        <div class="flex">
                            <div class="w-3/4 border border-secondary p-5 rounded-lg">
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

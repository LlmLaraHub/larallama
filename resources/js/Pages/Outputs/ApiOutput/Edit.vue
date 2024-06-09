<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {onMounted, ref} from 'vue';
import Intro from '@/Components/Intro.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import Resources from './Components/Resources.vue';
import { useForm } from '@inertiajs/vue3';
import {useToast} from "vue-toastification";
import Generate from "@/Pages/Outputs/WebPage/Components/Generate.vue";
import Delete from "@/Pages/Outputs/Components/Delete.vue";
import Templates from "@/Components/Templates.vue";

const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    output: {
        type: Object
    },
    prompts: Object,
    recurring: Object
});


const to_emails = ref("")

const form = useForm({
    title: props.output.title,
    summary: props.output.summary,
    active: props.output.active,
    recurring: props.output.recurring,
    meta_data: props.output.meta_data,
    token: "",
});

onMounted(() => {
    form.token = props.output.meta_data?.token
})

const choosePrompt = (prompt) => {
    form.summary = prompt;
}

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            meta_data: {
                token: form.token
            },
        }))
        .put(
        route('collections.outputs.api_output.update', {
            collection: props.collection.data.id,
            output: props.output.id
        }), {
            preserveScroll: true,
            onSuccess: params => {
                toast.info("Updated");
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

                    <form @submit.prevent="submit" class="">
                        <div class="flex">
                            <div class="w-3/4 border-secondary border rounded-none p-4">
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
                                        Back
                                    </SecondaryLink>

                                    <Delete :output="output"></Delete>
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

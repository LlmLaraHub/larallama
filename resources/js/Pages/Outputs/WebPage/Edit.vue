<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
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
    prompts: {
        type: Object
    },
});

const form = useForm({
    title: props.output.title,
    summary: props.output.summary,
    active: props.output.active,
    public: props.output.public,
});
const updateSummary = (summary) => {

    form.summary = summary;
}


const submit = () => {
    form.put(
        route('collections.outputs.web_page.update', {
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
    <AppLayout title="Edit Web Page">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl rounded-none p-5">
                    <Intro></Intro>

                    <form @submit.prevent="submit" class="p-10 ">
                        <div class="flex">
                            <div class="w-3/4 border border-secondary rounded-none p-5 rounded-lg">

                            <Resources
                        v-model="form">
                            <Generate :collection="collection.data"
                                      @generated="updateSummary"
                            ></Generate>
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

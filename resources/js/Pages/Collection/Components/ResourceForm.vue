<template>
    <div class="grid grid-cols-6 gap-4 ">

        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Name" />
            <TextInput v-model="modelValue.name" type="text" class="mt-1 block w-full"
            />
            <InputError :message="modelValue.errors.name" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Description (ai will use this to help understand the data)" />
            <TextArea v-model="modelValue.description" type="text"
                      class="mt-1 block w-full" />
            <InputError :message="modelValue.errors.description" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-6 ">
            <div>Choose the system to Interact with the data
            </div>
            <div v-if="activeLlms.length === 0" class="border border-secondary
            border-dashed rounded-md p-2">
                <div class="flex items-center justify-between gap-4">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>

                    No LLMs are active
                    You need to come back here after going to settings to active the LLM
                    <Link
                        :href="route('settings.show')" class="btn btn-secondary rounded-none">Settings</Link>
                </div>
            </div>
            <select v-else
                class="select select-bordered select-secondary w-full max-w-xs mt-2"
                v-model="modelValue.driver"
            >
                <option disabled>Choose one</option>
                <option v-for="option in activeLlms" :key="option.key"
                        :value="option.key">{{option.title}}</option>
            </select>
        </div>
        <div class="col-span-6 sm:col-span-6">
            <div>Choose the system to Embed the data</div>
            <div v-if="activeLlmsWithEmbeddings.length === 0" class="border border-secondary
            border-dashed rounded-md p-2">
                <div class="flex items-center justify-between gap-4">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>

                    No LLMs are active
                    You need to come back here after going to settings to active the LLM
                    <Link
                        :href="route('settings.show')" class="btn btn-secondary rounded-none">Settings</Link>
                </div>
            </div>
            <select
                v-else
                class="select select-bordered select-secondary w-full max-w-xs mt-2"
                v-model="modelValue.embedding_driver"
            >
                <option disabled>Choose one</option>
                <option v-for="option in activeLlmsWithEmbeddings" :key="option.key"
                        :value="option.key">{{option.title}}</option>
            </select>
        </div>
    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import EmbeddingType from './EmbeddingType.vue';
import Select from "@/Pages/Collection/Components/Select.vue";
import {computed} from "vue";
import {usePage, Link} from "@inertiajs/vue3";
const props = defineProps({
    modelValue: Object,
});


const activeLlmsWithEmbeddings = computed(() => {
    return usePage().props.active_llms_with_embeddings;
});


const activeLlms = computed(() => {
    return usePage().props.active_llms;
});

const typeChosen = (type) => {
    props.modelValue.driver = type;
}

const embeddingTypeChosen = (type) => {
    props.modelValue.embedding_driver = type;
}


</script>

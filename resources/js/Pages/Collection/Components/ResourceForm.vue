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
            <select
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
            <select
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
import {usePage} from "@inertiajs/vue3";
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

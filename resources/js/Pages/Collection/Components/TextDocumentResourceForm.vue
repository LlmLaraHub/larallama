<template>
    <div class="grid grid-cols-6 gap-4">

        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Name" />
            <TextInput v-model="modelValue.name" type="text" class="mt-1 block w-full" />
            <InputError :message="modelValue.errors.name" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Content" />

            <Editor
                v-if="usePage().props.features.editor"
                v-model="modelValue.content" />

            <TextArea v-else
                      v-model="modelValue.content" type="text"
                      class="w-full textarea textarea-secondary" rows="30"/>
            <InputError :message="modelValue.errors.content" class="mt-2" />
        </div>
    </div>
</template>

<script setup>


import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Editor from '@/Components/Editor.vue';
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    modelValue: Object,
});





</script>

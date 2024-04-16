<template>
    <div class="grid grid-cols-6 gap-4">

        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Name" />
            <TextInput v-model="modelValue.name" type="text" class="mt-1 block w-full" />
            <InputError :message="modelValue.errors.name" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-6">
            <InputLabel value="Content (Markdown is ideal)" />
            <TextArea v-model="modelValue.content" type="text" class="mt-1 block w-full" rows="30"/>
            <InputError :message="modelValue.errors.content" class="mt-2" />
        </div>
    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import LlmType from './LlmType.vue';
import EmbeddingType from './EmbeddingType.vue';
const props = defineProps({
    modelValue: Object,
});



</script>
<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Type here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Details"/>
            <textarea v-model="modelValue.details" class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="Add Your Search query here"></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>
    </div>
</template>

<script setup>

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
})
</script>
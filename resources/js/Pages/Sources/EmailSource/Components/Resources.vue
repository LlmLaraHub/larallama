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
                placeholder="This can assist the LLM to process your messages later."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>

        <div>
            <InputLabel value="Active"/>
            <input v-model="modelValue.active" type="checkbox"  />
            <InputError :message="modelValue.errors.active" />
        </div>


        <div>
            <InputLabel value="Recurring"/>
            <select
                class="select select-bordered w-full max-w-xs mt-2"
                v-model="modelValue.recurring">
                <option disabled selected>Types</option>
                <option v-for="option in recurring" :key="option.id" :value="option.id">
                    {{option.name}}
                </option>
            </select>
            <InputError :message="modelValue.errors.recurring" />
        </div>
    </div>
</template>

<script setup>

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})
</script>

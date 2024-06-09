<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Title Here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Prompt"/>
            <textarea v-model="modelValue.summary" class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="Page content here" rows="25"></textarea>
            <InputError :message="modelValue.errors.summary" />
        </div>
        <slot></slot>
        <div>
            <InputLabel value="To (comma seperated list of emails)"/>
            <input v-model="modelValue.to_emails" type="text" placeholder="bob@bobsburgers.com,linda@bobsburgers.com"
                   class="rounded-none input input-bordered w-full " />
        </div>


        <div class="form-control w-24">
            <label class="label cursor-pointer">
                <span class="label-text">Active</span>
                <input type="checkbox"
                       v-model="modelValue.active"
                       :checked="modelValue.active" class="checkbox" />
            </label>
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
import {ref} from "vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})



</script>

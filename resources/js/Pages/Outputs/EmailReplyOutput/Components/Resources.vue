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
                placeholder="Page content here" rows="15"></textarea>
            <InputError :message="modelValue.errors.summary" />
        </div>



        <div>
            <InputLabel value="Signature to put at end of the email"/>
            <textarea
                rows="10"
                v-model="modelValue.signature"
                placeholder="For further questions, please contact us at HR at +1(413)-225-1844 or info@larallama.io"
                class="rounded-none textarea textarea-bordered w-full mb-5"
            />
        </div>

        <div v-if="modelValue.errors.meta_data">
            <InputError v-for="message in modelValue.errors.meta_data" :message="message" />
        </div>

        <slot></slot>

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

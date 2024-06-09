<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Title Here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Summary"/>
            <textarea v-model="modelValue.summary" class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="Page content here" rows="25"></textarea>
            <InputError :message="modelValue.errors.summary" />
        </div>
        <slot></slot>


        <div class="form-control w-24">
            <label class="label cursor-pointer">
                <span class="label-text">Active</span>
                <input type="checkbox"
                       v-model="modelValue.active"
                       :checked="modelValue.active"
                       class="checkbox" />
            </label>
            <InputError :message="modelValue.errors.active" />
        </div>

        <div class="form-control w-24">
            <label class="label cursor-pointer">
                <span class="label-text">Public</span>
                <input type="checkbox"
                       v-model="modelValue.public"
                       :checked="modelValue.public" class="checkbox" />
            </label>
            <InputError :message="modelValue.errors.public" />
        </div>
    </div>
</template>

<script setup>

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {ref} from "vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object
})



</script>

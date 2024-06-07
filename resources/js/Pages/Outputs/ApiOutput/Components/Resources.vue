<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Title Here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Prompt used for response"/>
            <textarea v-model="modelValue.summary" class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="Just a bit describing the api" rows="25"></textarea>
            <InputError :message="modelValue.errors.summary" />
        </div>
        <slot></slot>
        <div>
            <InputLabel value="Token"/>
            <input v-model="modelValue.token" type="text" placeholder=""
                   class="rounded-none input input-bordered w-full " />
            <GenerateToken :size="32" @generatedToken="generatedToken"/>
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



    </div>
</template>

<script setup>

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {ref} from "vue";
import GenerateToken from "@/Components/GenerateToken.vue";



const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})

const generatedToken = (token) => {
    console.log('generatedToken', token);
    props.modelValue.token = token;
}


</script>

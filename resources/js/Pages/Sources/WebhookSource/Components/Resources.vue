<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Type here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Prompt to be used for Transformation"/>
            <textarea
                class="textarea textarea-bordered w-full"
                rows="5"
                v-model="modelValue.details"
                placeholder="The data will be passed to the LLM with your Prompt to transform the data."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>

        <div>
            <InputLabel value="Webhook Token"/>
            <input v-model="modelValue.secrets.token" type="text" placeholder="some-token-from-provider-or-you"
                   class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors?.secrets?.token" />
            <GenerateToken :size="32" @generatedToken="generatedToken"/>
        </div>

        <div>
            <InputLabel value="Active"/>
            <input v-model="modelValue.active" type="checkbox"  />
            <InputError :message="modelValue.errors.active" />
        </div>


        <div>
            <InputLabel value="Recurring"/>
            <select class="select select-bordered w-full max-w-xs mt-2" v-model="modelValue.recurring">
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
import InfoBox from "@/Components/InfoBox.vue";
import GenerateToken from "@/Components/GenerateToken.vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})

const generatedToken = (token) => {
    console.log('generatedToken', token);
    props.modelValue.secrets.token = token;
}

</script>

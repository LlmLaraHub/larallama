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
            <textarea
                rows="25"
                v-model="modelValue.details"
                class="rounded-none textarea textarea-bordered w-full mb-5 text-xl"
                placeholder="This can assist the LLM to process your messages later."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>
        <div>
            <InputLabel value="Add One or more URLs, one per line"/>
            <textarea
                rows="5"
                v-model="modelValue.meta_data.urls" type="text"
                placeholder="https://larallama.io/posts/numerous-ui-updates-prompt-template-improvements-and-more
https://docs.larallama.io/developing.html"
                class="rounded-none textarea textarea-bordered w-full mb-5 text-md"/>
            <InputError :message="modelValue.errors?.meta_data?.urls" />
        </div>

        <div>
            <InputLabel value="Active"/>
            <input v-model="modelValue.active" type="checkbox"  />
            <InputError :message="modelValue.errors.active" />
        </div>

        <div>
            <InputLabel value="Force Repeat"/>
            <input v-model="modelValue.force" type="checkbox"  />
            <InputError :message="modelValue.errors.force" />
            <div class="text-xs prose m-2">
                by default the system will only run the first time for a url or an email.
                But if you want to to try again just check this box.
                This can be good if you are checking a home page for updates.
                Or a feed for updates. But NOT if you are checking an email box for emails and
                do not want to repeat check the same email.
            </div>
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
import InfoBox from "@/Components/InfoBox.vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})
</script>

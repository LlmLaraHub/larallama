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
            <textarea v-model="modelValue.details"
                      rows="10"
                      class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="This can assist the LLM to process your messages later."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>

        <div class="border-secondary border rounded p-5">
            <h2>These will be encrypted</h2>

            <div class="mb-2 mt-2">
                <InputLabel value="IMAP Username"/>
                <input v-model="modelValue.secrets.username" type="text" placeholder="bob@bobsburgers.com"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.secrets?.username" />
            </div>
            <div class="mb-2 mt-2">
                <InputLabel value="IMAP Password"/>
                <input v-model="modelValue.secrets.password" type="text" placeholder="Type here"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.secrets?.password" />
            </div>

            <div class="mb-2 mt-2">
                <InputLabel value="IMAP Host"/>
                <input v-model="modelValue.secrets.host" type="text" placeholder="mail.privateemail.com"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.secrets?.host" />
            </div>

            <div class="mb-2 mt-2">
                <InputLabel value="Port"/>
                <input v-model="modelValue.secrets.port" type="text" placeholder="993"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.secrets?.port" />
            </div>
            <div class="mb-2 mt-2">
                <InputLabel value="Email Box"/>
                <input v-model="modelValue.secrets.email_box" type="text" placeholder="Type here"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.secrets?.email_box" />
                <InfoBox>
                    This is the one box it will check.
                </InfoBox>
            </div>

        </div>




        <div>
            <InputLabel value="Delete"/>
            <input v-model="modelValue.secrets.delete" type="checkbox"  />
            <InputError :message="modelValue.errors?.secrets?.delete" />
            <InfoBox>
                Delete emails after they are processed
            </InfoBox>
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
import InfoBox from "@/Components/InfoBox.vue";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})
</script>

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
                required
                rows="10"
                v-model="modelValue.meta_data.signature"
                placeholder="For further questions, please contact us at HR at +1(413)-225-1844 or info@larallama.io"
                class="rounded-none textarea textarea-bordered w-full mb-5"
            />
        </div>

        <div v-if="modelValue.errors.meta_data">
            <InputError v-for="message in modelValue.errors.meta_data" :message="message" />
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
            <InputLabel value="Persona to answer in?"/>
            <select

                class="select select-bordered w-full max-w-xs mt-2"
                v-model="modelValue.persona_id">
                <option disabled selected>Types</option>
                <option v-for="persona in usePage().props.personas.data"
                        :key="persona.id" :value="persona.id">
                    {{persona.name}}
                </option>
            </select>
            <InputError :message="modelValue.errors.persona_id" />
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
import InfoBox from "@/Components/InfoBox.vue";
import {usePage} from "@inertiajs/vue3";

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})



</script>

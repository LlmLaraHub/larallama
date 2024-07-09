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
                rows="10"
                v-model="modelValue.details"
                class="rounded-none textarea textarea-bordered w-full mb-5  text-xl"
                placeholder="This can assist the LLM to process your messages later."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>

        <div class="border-gray-300 border rounded p-10">
            <h2>Add your Google Sheet Info</h2>

            <div>
                <InputLabel value="Sheet ID"/>
                <div>
                    Once you make the Share Public you just need to get the ID of the sheet.
                    It is the long string like this "1Joj-iYc95wV-TFrBo_v_8bOX77QZP247EX1ot5sQGfs"
                </div>
                <textarea
                    rows="10"
                    v-model="modelValue.meta_data.sheet_id" type="text"
                    placeholder="1Joj-iYc95wV-TFrBo_v_8bOX77QZP247EX1ot5sQGfs"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.meta_data?.sheet_id" />
            </div>

            <div>
                <InputLabel value="Sheet Name"/>
                <div>
                    This is the name in the bottom tab. Capitalization counts.
                </div>
                <textarea
                    rows="10"
                    v-model="modelValue.meta_data.sheet_name" type="text"
                    placeholder="DATABASE"
                    class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.meta_data?.sheet_name" />



                <div>
                    <div class="flex w-full justify-end">
                        <button
                            :disabled="gettingFeed || (!modelValue.meta_data.sheet_id && !modelValue.meta_data.sheet_name)"
                            type="button" class="btn btn-sm btn-outline rounded-none mt-2" @click="testFeed">
                            Test Google Sheet
                        </button>
                    </div>
                    <div>
                        Count: {{ feedResults.count }}

                        <div>
                            <div v-for="item in feedResults.items" :key="item.id">

                                {{ item }}

                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";

const toast = useToast();
const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})

const feedResults = ref([]);

const gettingFeed = ref(false);

const form = useForm({
    sheet_id: "",
    sheet_name: "",
});

const testFeed = () => {
    gettingFeed.value = true;
    toast('Getting data', {position: "bottom-right"});
    form.sheet_id = props.modelValue.meta_data.sheet_id;
    form.sheet_name = props.modelValue.meta_data.sheet_name;
    axios.post(route('sources.google_sheet_source.test_feed'), {
        sheet_id: form.sheet_id,
        sheet_name: form.sheet_name,
    }).then(response => {
        gettingFeed.value = false;
        console.log(response.data);
        feedResults.value = response.data;
        toast('Got feed', {position: "bottom-right"});
    }).catch(error => {
        gettingFeed.value = false;
        toast.error('Error getting feed', {position: "bottom-right"});
        console.log(error);
    })
}
</script>

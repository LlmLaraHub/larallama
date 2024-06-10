<template>
    <div class="flex flex-col space-y-8 mt-4">
        <div>
            <InputLabel value="Title"/>
            <input v-model="modelValue.title" type="text" placeholder="Type here"
                class="rounded-none input input-bordered w-full " />
            <InputError :message="modelValue.errors.title" />
        </div>

        <div>
            <InputLabel value="Prompt"/>
            <textarea
                rows="15"
                v-model="modelValue.details" class="rounded-none textarea textarea-bordered w-full mb-5"
                placeholder="This can assist the LLM to process your messages later."></textarea>
            <InputError :message="modelValue.errors.details" />
        </div>

        <div class="border border-secondary rounded p-10">
            <h2>This is meta data</h2>

            <div>
                <InputLabel value="Example"/>
                <input v-model="modelValue.meta_data.feed_url" type="text" placeholder="https://www.larallama.io/feed"
                       class="rounded-none input input-bordered w-full " />
                <InputError :message="modelValue.errors?.meta_data?.feed_url" />

                <div>
                    <div class="flex w-full justify-end">
                        <button
                            :disabled="gettingFeed || !modelValue.meta_data.feed_url"
                            type="button" class="btn btn-sm btn-outline rounded-none mt-2" @click="testFeed">
                            Test Feed
                        </button>
                    </div>
                    <div>
                        Count: {{ feedResults.count }}

                        <div>
                            <div v-for="item in feedResults.items" :key="item.id">

                                <a
                                    class="link"
                                    target="_blank"
                                    :href="item.link">{{ item.title }}</a>

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
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {useToast} from "vue-toastification";

const toast = useToast();

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: Object,
    recurring: Object
})


const form = useForm({url: ""});

const feedResults = ref([]);

const gettingFeed = ref(false);

const testFeed = () => {
    gettingFeed.value = true;
    toast('Getting', {position: "bottom-right"});
    form.url = props.modelValue.meta_data.feed_url;
    axios.post(route('sources.feed_source.test_feed'), {
        url: form.url
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

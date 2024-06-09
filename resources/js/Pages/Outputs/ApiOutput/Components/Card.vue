<script setup>

import {Link, useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import Settings from "@/Pages/Outputs/Components/Settings.vue";
import {ref} from "vue";

const props = defineProps({
    output: Object
})

const toast = useToast();

const showToken = ref(false)

</script>

<template>
    <div class="card rounded-none w-96 dark:bg-neutral shadow-xl">
        <div class="card-body">
            <Settings :output="output"></Settings>

            <div class="text-xs  flex justify-start gap-2 items-center">
                <span @click="showToken = !showToken" class="link">Token:</span>
                    <span
                        v-if="!showToken"
                        class="text-primary">******************</span>
                    <span
                        v-else class="font-bold ">{{ output.meta_data?.token }}</span>
            </div>

            <div class="text-xs">
                URL: <span class="font-bold">{{ output.url }}</span>
            </div>
            <div class="card-actions justify-between flex items-center">
                <span class="badge badge-default">{{ output.type_formatted}}</span>
                <div class="flex justify-end gap-2 items-center">
                    <Link :href="route('collections.outputs.api_output.edit', {
                                        collection: output.collection_id,
                                        output: output.id
                                    })" class="btn btn-primary rounded-none">Edit</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

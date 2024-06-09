<script setup>

import {Link, useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import Settings from "@/Pages/Outputs/Components/Settings.vue";

const props = defineProps({
    output: Object
})

const toast = useToast();

const form = useForm({})

const send = () => {
    form.post(route('collections.outputs.email_output.send', {
        output: props.output.id
    }), {
        preserveScroll: true,
        onSuccess: params => {
            toast("Sending mail will take a moment to send 💌💌💌");
        },
        onError: params => {
            toast.error("Oops error see the logs sorry :(")
        }
    })
}
</script>

<template>
    <div class="card rounded-none w-96 dark:bg-neutral shadow-xl">
        <div class="card-body">

            <Settings :output="output"></Settings>

            <div class="card-actions justify-between flex items-center">
                <span class="badge badge-default">{{ output.type_formatted}}</span>
                <div class="flex justify-end gap-2 items-center">
                    <button @click="send" type="button" class="btn btn-neutral rounded-none" :disabled="form.processing">
                        <span v-if="!form.processing">
                            send mail
                        </span>
                        <span v-else class="loading loading-infinity loading-md"></span>
                    </button>
                    <Link :href="route('collections.outputs.email_output.edit', {
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

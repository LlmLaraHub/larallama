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
    form.post(route('collections.outputs.email_reply_output.check', {
        output: props.output.id
    }), {
        preserveScroll: true,
        onSuccess: params => {
            toast("Checking mail will take a moment to send 💌💌💌");
        },
        onError: params => {
            toast.error("Oops error see the logs sorry :(")
        }
    })
}
</script>

<template>
    <div class="card rounded-none w-96  shadow-xl  border border-neutral">
        <div class="card-body">



            <Settings :output="output"></Settings>
            <div class="text-xs">
                Signature: <span class="font-bold">{{ output.meta_data.signature }}</span>
            </div>
            <div class="text-xs">
                Persona Of: <span class="font-bold">{{ output.persona?.name }}</span>
            </div>


            <div class="text-xs">
                Type: <span class="badge badge-default">{{ output.type_formatted}}</span>
            </div>
                <div class="flex justify-end gap-2 items-center">
                    <button @click="send" type="button" class="btn btn-secondary btn-md rounded-none" :disabled="form.processing">
                        <span v-if="!form.processing">
                            Check Mail
                        </span>
                        <span v-else class="loading loading-infinity loading-md"></span>
                    </button>
                    <Link :href="route('collections.outputs.email_reply_output.edit', {
                                        collection: output.collection_id,
                                        output: output.id
                                    })" class="btn btn-primary rounded-none">Edit</Link>
                </div>
        </div>
    </div>
</template>

<style scoped>

</style>

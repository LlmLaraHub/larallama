<script setup>

import {Link, useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import Settings from "@/Pages/Sources/Cards/Settings.vue";
import Clipboard from "@/Components/Clipboard.vue";
import {computed} from "vue";
const toast = useToast();

const props = defineProps({
    source: Object
})

const form = useForm({})



const run = (source) => {
    form.post(route('collections.sources.run', {
        source: source.id
    }), {
        onStart: params => {
          toast("Running");
        },
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Source is running');
        }
    });
}

</script>

<template>
    <div class="card rounded-none w-96 dark:bg-neutral shadow-xl" :key="source.id">
        <div class="card-body">
            <Settings :source="source"/>
            <div class="text-xs">
                Feed URL: <span class="font-bold ">
                    <Clipboard :content="source.meta_data.feed_url">
                        {{ source.meta_data.feed_url}}
                    </Clipboard>
            </span>
            </div>

            <div class="card-actions justify-end">
                <button @click="run(source)" type="button" class="btn btn-primary rounded-none">Run</button>
                <Link :href="route('collections.sources.feed_source.edit', {
                                    collection: source.collection_id,
                                    source: source.id
                                })" class="btn btn-primary rounded-none">Edit</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

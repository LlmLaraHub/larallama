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

const email = computed(() => {
    return `assistant+${ props.source.slug }@laralamma.ai`
})

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
    <div class="card rounded-none w-96 bg-base-100 shadow-xl" :key="source.id">
        <div class="card-body">
            <h2 class="card-title text-gray-600">{{ source.title }} <span class="text-sm">#{{source.id}}</span></h2>
            <Settings :source="source"/>

            <div class="text-xs">
                Assistant Email: <span class="font-bold text-gray-400">
                    <Clipboard :content="email">{{ email}}</Clipboard>
            </span>
            </div>
            <div class="card-actions justify-end">
                <button @click="run(source)" type="button" class="btn btn-primary rounded-none">Run</button>
                <Link :href="route('collections.sources.websearch.edit', {
                                    collection: source.collection_id,
                                    source: source.id
                                })" class="btn btn-primary rounded-none">Edit</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

<script setup>

import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    source: Object
})

const form = useForm({})
const submit = () => {
    form.delete(route('collections.sources.delete', {
        source: props.source.id
    }), {
        preserveScroll: true,
    });
}
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex justify-center">
            <button type="submit" class="btn btn-warning rounded-none">Delete</button>
        </div>
    </form>
</template>

<style scoped>

</style>

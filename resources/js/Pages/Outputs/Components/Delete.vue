<script setup>

import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    output: Object
})

const form = useForm({})
const submit = () => {
    form.delete(route('collections.outputs.delete', {
        output: props.output.id
    }), {
        preserveScroll: true,
    });
}
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex justify-center">
            <button type="submit" class="btn btn-warning btn-sm">Delete</button>
        </div>
    </form>
</template>

<style scoped>

</style>

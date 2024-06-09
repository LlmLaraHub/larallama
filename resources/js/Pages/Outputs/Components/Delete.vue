<script setup>

import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    output: Object
})

const url = route('collections.outputs.delete',
    {output: props.output.id}
);

const form = useForm({})
const submit = () => {
    form.delete(url, {
        preserveScroll: true,
    });
}
</script>

<template>
        <div class="flex justify-center">
            <button type="button" @click="submit" class="btn btn-warning rounded-none">Delete</button>
        </div>
</template>

<style scoped>

</style>

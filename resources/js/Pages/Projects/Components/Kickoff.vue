<script setup>

import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";

const kickOffRunning = ref(true)

const props = defineProps({
    project: Object
})

const form = useForm({})

const kickoff = () => {
    kickOffRunning.value = true;
    form.post(route("projects.kickoff", {
        project: props.project.id
    }), {
        preserveScroll: true,
        preserveState: false,
        onFinish: params => {
            kickOffRunning.value = false;
        }

    })
}
</script>

<template>
    <button
        :disabled="form.processing"
        class="btn btn-secondary rounded-none"
        @click="kickoff" type="button">
                                <span v-if="!form.processing">
                                    Kick off
                                </span>
        <span v-else class="loading loading-infinity loading-sm text-black"></span>
    </button>
</template>

<style scoped>

</style>

<script setup>
const emit = defineEmits(['generated'])

import {ref} from "vue";

const loading = ref(false)

const props = defineProps({
    collection: Object
})

const generateSummary = () => {
    loading.value = true;
    axios.post(route(
        'collections.outputs.web_page.summary', {
            collection: props.collection
        }
    )).then(response => {
        loading.value = false
        console.log(response.data)
        emit('generated', response.data.summary)
    })
}

</script>

<template>
    <div>
        Generate Summary from Collection
        <button type="button"
                :disabled="loading"
                class="btn btn-secondary flex items-center rounded-none"
                @click="generateSummary">
            generate
            <span v-if="loading" class="loading loading-dots loading-sm"></span>
        </button>
    </div>
</template>

<style scoped>

</style>

<script setup>
import {computed, onMounted, onUnmounted, ref} from "vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    collection: Array
})

const notCompleteCount = computed(() => {
    return documents.value.filter(document => document.status !== 'Complete').length;
})

const completeCount = computed(() => {
    return documents.value.filter(document => document.status === 'Complete').length;
})

const totalCount = computed(() => {
    return documents.value.length;
})

const percentComplete = computed(() => {
    return Math.round((completeCount.value / totalCount.value) * 100);
})

const documents = ref([]);

const getDocuments = () => {
    axios.get(route('collections.documents.status', {
        collection: props.collection.id
    })).then(response => {
        documents.value = response.data.documents;
    }).catch(error => {
        console.log(error)
    })
}

onUnmounted(() => {
    Echo.leave(`collection.${props.collection.id}`);
});

onMounted(() => {
    Echo.private(`collection.${props.collection.id}`)
        .listen('.status', (e) => {
            let message = e.message;
            if (message === 'Document Processed' || message === 'processing') {
                getDocuments();
            }
        });
    getDocuments();
})

</script>

<template>

    <div  v-show="documents.length > 0" v-auto-animate>
        <div class="flex justify-start gap-2 items-center text-sm border border-secondary p-2 rounded-md">
            <div>Total: {{totalCount}}</div>
            <div>Complete: {{completeCount}}</div>
            <div>Not Complete: {{notCompleteCount}}</div>
            <div class="radial-progress text-xs text-secondary"

                 :style="`--value:${percentComplete}; --size:3rem; `" role="progressbar">{{percentComplete}}%</div>
        </div>
    </div>

</template>

<style scoped>

</style>

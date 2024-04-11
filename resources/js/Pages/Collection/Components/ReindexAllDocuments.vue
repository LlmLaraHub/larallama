<template>

<form @submit.prevent="submit">
    <PrimaryButton type="submit">
    Reindex All Documents</PrimaryButton>
</form>

</template>

<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { defineProps, defineEmits } from 'vue';

const toast = useToast();

const emit = defineEmits(['reindexed']);
const props = defineProps({
    collection: Object
});

const form = useForm({});

const submit = () => {
    toast.info("Running reindexing jobs...")
    emit('reindexed');
}


</script>
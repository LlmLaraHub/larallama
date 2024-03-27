<template>
    <SecondaryButton 
    type="button"
    @click="start"
    class="flex justify-between items-center gap-4">
        <ChatBubbleLeftIcon class="h-5 w-5"></ChatBubbleLeftIcon>
        start a new chat</SecondaryButton>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';

const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
})

const form = useForm({});


const start = () => {
    form.post(route('chats.collection.store', {
        collection: props.collection.id
    }), {
        onError: () => {
            toast.error('Failed to start chat :(');
        },
    });
}
</script>
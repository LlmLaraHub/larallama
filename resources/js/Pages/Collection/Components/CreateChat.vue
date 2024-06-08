<template>
    <button
    type="button"
    @click="start"
    :class=classValue
    class="flex justify-between items-center gap-4">

        <slot>
            Chat with your Collection
        </slot>


    </button>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';

const toast = useToast();

const props = defineProps({
    classValue: String,
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

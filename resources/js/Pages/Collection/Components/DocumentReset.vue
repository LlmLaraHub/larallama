<template>

    <button class="text-gray-500 text-sm flex justify-start gap-2 items-center" @click="reset" :disabled="form.processing">
    reset
    <ArrowPathIcon class="h-4 w-4"/>
    
    </button>
</template>

<script setup>
import {useForm} from '@inertiajs/vue3'
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
const emit = defineEmits(['reset'])

const form = useForm({})

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    document: {
        type: Object,
        required: true,
    },
});

const reset = () => {
    form.post(route('collections.documents.reset', {
        collection: props.collection.id,
        document: props.document.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            emit('reset')
        }
    });
}

</script>
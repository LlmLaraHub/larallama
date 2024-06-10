<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4 bg-base-100">
            <div class="text-lg font-medium">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm">
                <slot name="content" />
            </div>
        </div>

        <div class="bg-base-100 flex flex-row justify-end px-6 py-4 text-end dark:bg-neutral">
            <slot name="footer" />
        </div>
    </Modal>
</template>

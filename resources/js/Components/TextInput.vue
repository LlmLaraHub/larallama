<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="input w-full ring-pink-500 ring-opacity-50 focus:ring-pink-600 focus:ring-opacity-100"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

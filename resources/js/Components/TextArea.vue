<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);


</script>

<template>
    <textarea
        ref="input"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-gray-800 dark:text-gray-400"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
    </textarea>
</template>

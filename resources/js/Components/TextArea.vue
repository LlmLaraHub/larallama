<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    rows: {
        type: Number,
        default: 10,
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);


</script>

<template>
    <textarea
        ref="input"
        :rows="rows"
        class="textarea textarea-bordered"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
    </textarea>
</template>

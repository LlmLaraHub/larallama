<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    options: Object
});

defineEmits(['update:modelValue']);


</script>

<template>

    <select
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
        <option disabled>Choose one</option>
        <option v-for="option in options" :key="option.id"
        :value="option.id">{{option.name}}</option>
    </select>


</template>

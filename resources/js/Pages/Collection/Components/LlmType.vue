<template>

    <select
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
        <option disabled>Choose one</option>
        <option v-for="option in activeLlms" :key="option.key"
                :value="option.key">{{option.title}}</option>
    </select>

    <div class="text-white">{{ modelValue }}</div>
  </template>

<script setup>
import {computed, ref, watch} from 'vue'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'
import {usePage} from "@inertiajs/vue3";
import Select from "@/Pages/Collection/Components/Select.vue";

defineEmits(['update:modelValue']);

defineProps({
  modelValue: String,
});

const activeLlms = computed(() => {
  return usePage().props.active_llms;
});


  </script>

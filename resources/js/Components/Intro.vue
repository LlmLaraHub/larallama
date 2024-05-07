<template>
<h3 class="text-base font-semibold leading-6 text-gray-900">
    <slot></slot>
</h3>

<div class="mt-2 max-w-4xl text-sm text-gray-500">
          <slot name="description"></slot>
</div>
</template>

<script setup>

const props = defineProps({
    collection: Object
})
</script>
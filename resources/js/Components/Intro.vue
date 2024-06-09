<template>
<h3 class="font-semibold leading-6">
    <slot></slot>
</h3>

<div class="mt-2 max-w-4xl text-sm">
          <slot name="description"></slot>
</div>
</template>

<script setup>

const props = defineProps({
    collection: Object
})
</script>

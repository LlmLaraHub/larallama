<template>
<h3 class="font-semibold leading-6">
    <slot>
        {{ usePage().props.type}}
    </slot>
</h3>

<div class="mt-2 max-w-4xl text-sm">
          <slot name="description">
              {{ usePage().props.info}}
          </slot>
</div>
</template>

<script setup>

import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    collection: Object
})
</script>

<script setup>

import Clipboard from "@/Components/Clipboard.vue";
</script>

<template>
    <div class="text-sm mt-2">
        <div>
            <div class="flex justify-start gap-2 items-center">[CONTEXT] <Clipboard content="[CONTEXT]"></Clipboard>

            </div>
            <div class="p-2">this is all the data the system finds to answer the users question</div>
        </div>
        <div>
            <div class="flex justify-start gap-2 items-center">[USER_INPUT] <Clipboard content="[USER_INPUT]"></Clipboard></div>
            <div class="p-2">this is all the data the system finds to answer the users question</div>
        </div>
    </div>
</template>

<style scoped>

</style>

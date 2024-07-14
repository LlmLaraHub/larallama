<script setup>

import Clipboard from "@/Components/Clipboard.vue";
</script>

<template>
    <div class="text-sm mt-2">
        <div>
            <div class="flex justify-start gap-2 items-center">[CONTEXT] <Clipboard content="[CONTEXT]"></Clipboard>

            </div>
            <div class="p-2">This is the content of the search, email, document etc. The system will fill this in with the data it finds</div>
        </div>
        <div>
            <div class="flex justify-start gap-2 items-center">[USER_INPUT] <Clipboard content="[USER_INPUT]"></Clipboard></div>
            <div class="p-2">
                This is the data from the prompt you type in.
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

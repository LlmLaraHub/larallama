<script setup>

const props = defineProps({
    message: Object
})


</script>

<template>
    <div class="flex justify-between items-center -mb-2">
        <div class="flex justify-start text-sm gap-2 items-center w-3/4">
            <div v-if="message.meta_data?.persona" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Persona being used: </span>
                <span class="font-bold">{{message.meta_data.persona}}</span>
            </div>
            <div v-if="message.meta_data?.filter" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Filter being used: </span>
                <span class="font-bold">{{message.meta_data.filter.name}}</span>
            </div>
            <div v-if="message.meta_data?.driver" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary w-20">
                            LLM Used: </span>
                        <span class="font-bold">{{message.meta_data.driver}}</span>
            </div>
            <div v-if="message.meta_data?.date_range" class="flex justify-start gap-1 items-center">
                        <span class="text-secondary">
                            Date Range used: </span>
                <span class="font-bold">{{message.meta_data.date_range}}</span>
            </div>

            <div v-if="
            message.meta_data?.tool && message.tools?.tools?.length === 0" class="flex justify-start gap-2 items-center">
                        <span class="text-secondary">
                            Tool used: </span>
                <span class="font-bold">{{message.meta_data.tool}}</span>
            </div>

            <div v-if="
            message.tools?.tools?.length > 0" class="flex justify-start gap-1 items-center"
            >
                        <span class="text-secondary  w-20">
                            Tools used: </span>
                        <span
                            v-for="tool in message.tools.tools" :key="tool.function_name"
                            class="font-bold">{{tool.function_name}}</span>
            </div>
        </div>

        <div class="w-full flex justify-end gap-2 items-center" v-if="message.from_ai === false">
            <slot></slot>
        </div>
    </div>
</template>

<style scoped>

</style>

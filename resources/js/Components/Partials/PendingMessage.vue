<template>
    <div class="flex w-full mt-2 space-x-3 max-w-xs ml-auto justify-end">
        <div>
            <div class="bg-blue-600 text-white p-3 rounded-l-lg rounded-br-lg">
                <div class="flex space-x-2">
                    <div class="h-2 w-2 bg-gray-100 rounded-full animate-pulse"></div>
                    <div class="h-2 w-2 bg-gray-100 rounded-full animate-pulse delay-150"></div>
                    <div class="h-2 w-2 bg-gray-100 rounded-full animate-pulse delay-300"></div>
                </div>
            </div>
        </div>
        <img class="rounded-full h-8 w-8" src="/images/writer_small.jpeg">
    </div>
</template>

<script>
export default {
    name: "PendingMessage"
}
</script>

<style scoped>

</style>

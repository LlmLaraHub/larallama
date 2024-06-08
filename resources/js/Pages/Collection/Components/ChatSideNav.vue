<script setup>
import CreateChat from "@/Pages/Collection/Components/CreateChat.vue";
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    chats: Object,
    collection: Object
})
</script>

<template>
<div>
    <div>
        <div>
            <CreateChat
                class-value="btn btn-primary w-full"
                 :collection="collection" >
                <template #default>
                    <div class="flex items-center gap-2">
                        <img src="/images/logo.png" class="h-8 w-8" />
                        <span class="text-white">New Chat</span>
                    </div>
                </template>


            </CreateChat>
        </div>
    </div>
    <div class="mt-2">
        <div v-if="chats.data.length === 0">
            No chats yet
        </div>
        <div class="flex flex-col gap-2">
            <template v-for="chat in chats.data" :key="chat.id">
            <Link
                class="
                rounded-sm hover:bg-secondary
                hover:text-neutral-50
                px-2 py-1
                text-sm w-full text-left"
                :class="route().current('chats.collection.show', {
                collection: collection.id,
                chat: chat.id
                }) ? 'bg-secondary text-neutral-50' : ''"
                :href="route('chats.collection.show', {
                collection: collection.id,
                chat: chat.id
            })">
                <span class="truncate w-3/4">{{ chat.title }}</span>
            </Link>
            </template>
        </div>
    </div>
</div>

</template>

<style scoped>

</style>

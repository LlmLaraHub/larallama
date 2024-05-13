<template>
    <div class="shadow-inner-top navbar bg-gray-300 flex justify-center gap-4">
        <Link :class="{ 'active btn btn-sm': route().current('collections.index') }" :href="route('collections.index')">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        </Link>
        <Link :class="{ 'active btn btn-sm': route().current('collections.show') }" :href="route('collections.show', {
            collection: collection.id
        })">Collection</Link>
        <CreateChat v-if="!chat?.id" :collection="collection" />
        <Link v-if="chat?.id" :class="{ 'active btn btn-sm': route().current('chats.collection.show') }" :href="route('chats.collection.show', {
            collection: collection.id,
            chat: chat?.id
        })">Chat with your Collection</Link>
        <Link :class="{ 'active btn btn-sm': route().current('collections.sources.index') }" :href="route('collections.sources.index', {
            collection: collection.id
        })">Sources</Link>
        <Link :class="{ 'active btn btn-sm': route().current('collections.outputs.index') }" :href="route('collections.outputs.index', {
            collection: collection.id
        })">Outputs</Link>
    </div>

</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CreateChat from './CreateChat.vue';
const props = defineProps({
    collection: Object,
    chat: Object
})

</script>

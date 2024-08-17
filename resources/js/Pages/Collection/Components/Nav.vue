<template>
    <div class="shadow-inner-top navbar flex justify-center gap-4 shadow shadow-bottom">
        <Link class="btn btn-circle btn-outline btn-sm" :href="route('collections.index')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        </Link>
        <NavButton
            name="collections.show"
            :href="route('collections.show', {collection: collection.id})"
        >
            <div class="tooltip tooltip-info"
                 data-tip="This is your context for all your chat interactions and automations">
                <div class="flex gap-2 items-center">
                    Collection
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </NavButton>

        <NavButton v-if="chat?.id"
                   name="chats.collection.show"
            :href="route('chats.collection.show', {
            collection: collection.id,
            chat: chat?.id
        })"
        >Chat with your Collection</NavButton>
        <CreateChat
            class-value=" btn btn-sm"
            v-else :collection="collection" />
        <NavButton
            name="collections.sources.index"
                   :href="route('collections.sources.index', {
            collection: collection.id
        })"
        >
            <div class="tooltip tooltip-info"
                 data-tip="Upload files or use our Sources to get content from Emails, Web Pages etc.">
                <div class="flex gap-2 items-center">
                    Add Content to Collection
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </NavButton>

        <NavButton
            name="collections.outputs.index"
            :href="route('collections.outputs.index', {
            collection: collection.id
        })"
        >
            <div class="tooltip tooltip-info"
                 data-tip="Numerous way to share your collection with other systems like Email, Webhooks, APIs and more">
                <div class="flex gap-2 items-center">
                    Share Collection
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </NavButton>
    </div>

</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CreateChat from './CreateChat.vue';
import NavLink from "@/Components/NavLink.vue";
import NavButton from "@/Components/NavButton.vue";
const props = defineProps({
    collection: Object,
    chat: Object
})

</script>

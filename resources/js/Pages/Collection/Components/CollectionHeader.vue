<template>
    <div class="border-b pb-5 px-3 py-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-semibold leading-6 text-gray-900">{{ collection.name }}</h3>
            <div class="flex justify-end gap-2 items-center">

                <CollectionTags :collection="collection"></CollectionTags>
                
                <details class="dropdown dropdown-end">
                    <summary class="m-1 btn border-none">
                        <EllipsisVerticalIcon class="h-5 w-5" />
                    </summary>
                    <ul class="p-2 shadow menu dropdown-content z-[49] w-52">
                        <li>
                            <button type="button" class="btn-link" @click="showEditCollectionSlideOut">Edit</button>
                        </li>
                        <li>
                            <button type="button" class="btn-link" @click="toggleReindexCollection">Reindex
                                Documents</button>
                        </li>
                    </ul>
                </details>
            </div>

        </div>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">
            {{ collection.description }}
        </p>
    </div>
</template>

<script setup>

import Welcome from '@/Components/Welcome.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CollectionTags from './CollectionTags.vue';
import Label from '@/Components/Labels.vue';
import CreateChat from './CreateChat.vue';
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';
import { EllipsisVerticalIcon } from '@heroicons/vue/24/solid';


const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    chat: {
        type: Object
    }
});
</script>
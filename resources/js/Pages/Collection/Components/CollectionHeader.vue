<template>
    <div class="pb-5 px-3 py-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-semibold leading-6">{{ collection.name }}</h3>
            <div class="flex justify-end gap-2 items-center">

                <CollectionTags :collection="collection"></CollectionTags>

                <details ref="openMenu" class="dropdown" v-if="showEdit">
                    <summary class="m-1 btn btn-ghost border-neutral">
                        <EllipsisVerticalIcon class="h-5 w-5" />
                    </summary>
                    <ul class="p-2 shadow menu dropdown-content
                     -ml-24
                    z-[1] bg-base-100  border border-neutral rounded-box w-52">
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
        <p class="mt-2 max-w-4xl text-sm">
            {{ collection.description }}
        </p>
    </div>
</template>

<script setup>

import SecondaryLink from '@/Components/SecondaryLink.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CollectionTags from './CollectionTags.vue';
import Label from '@/Components/Labels.vue';
import CreateChat from './CreateChat.vue';
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';
import { EllipsisVerticalIcon } from '@heroicons/vue/24/solid';
import {ref} from "vue";

const openMenu = ref(null);

const closeMenu = () => {
    openMenu.value.removeAttribute('open')
}

const props = defineProps({
    showEdit: {
        type: Boolean,
        default: true,
    },
    collection: {
        type: Object,
        required: true,
    },
    chat: {
        type: Object
    }
});

const emit = defineEmits(['showEditCollectionSlideOut', 'toggleReindexCollection']);

const showEditCollectionSlideOut = () => {
    closeMenu();
    emit('showEditCollectionSlideOut');
};

const toggleReindexCollection = () => {
    closeMenu();
    emit('toggleReindexCollection');
};

</script>

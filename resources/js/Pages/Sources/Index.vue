<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';
import Nav from '@/Pages/Collection/Components/Nav.vue';

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    sources: {
        type: Object
    },
    documents: {
        type: Object,
    },
    chat: {
        type: Object,
    },
});



</script>

<template>
    <AppLayout title="Sources">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sources
            </h2>
        </template>

        <Nav :collection="collection.data" :chat="chat?.data"></Nav>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-10">
                  Sources Here
                </div>
            </div>
        </div>
    </AppLayout>
</template>

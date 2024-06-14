<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import Persona from "./Partials/Persona.vue";
import Images from "@/Pages/StyleGuide/Partials/Images.vue";
import SocialLinks from "@/Pages/StyleGuide/Partials/SocialLinks.vue";

const props = defineProps({
    personas: Object,
});
</script>

<template>
    <AppLayout title="Style Guides">

        <div class="mb-4 prose font-bold border mx-auto p-10 border-secondary mt-4
        bg-base-100 dark:bg-base-200 shadow-xl rounded-lg w-full
items-center flex justify-start gap-4
">

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>

            Here you can add style to your responses including images!
        </div>
        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <Persona :personas="personas.data"/>
                    <SectionBorder/>
                </div>
                <div>
                    <Images :images="{}"/>
                    <SectionBorder/>
                </div>
                <div>
                    <SocialLinks :socials="{}"/>
                    <SectionBorder/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

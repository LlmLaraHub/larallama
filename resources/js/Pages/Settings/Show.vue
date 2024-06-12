<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import OpenAiSecrets from "@/Pages/Settings/Partials/OpenAiSecrets.vue";
import ClaudeSecrets from "@/Pages/Settings/Partials/ClaudeSecrets.vue";
import OllamaApiSecrets from "@/Pages/Settings/Partials/OllamaApiSecrets.vue";
import GroqSecrets from "@/Pages/Settings/Partials/GroqSecrets.vue";

const props = defineProps({
    setting: Object,
});
</script>

<template>
    <AppLayout title="Settings">

        <div class="mb-4 prose font-bold border mx-auto p-10 border-secondary mt-4
        bg-base-100 dark:bg-base-200 shadow-xl rounded-lg w-full">
            You can add as many services as you want from below and then use different
            services for different Collections.
        </div>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
               <div>
                   <OpenAiSecrets :setting="setting.data"/>
                   <SectionBorder/>
               </div>

                <div>
                    <ClaudeSecrets :setting="setting.data"/>
                    <SectionBorder/>
                </div>

                <div>
                    <OllamaApiSecrets :setting="setting.data"/>
                    <SectionBorder/>
                </div>

                <div>
                    <GroqSecrets :setting="setting.data"/>
                    <SectionBorder/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

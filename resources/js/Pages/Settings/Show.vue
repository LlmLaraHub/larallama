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

        <div class="py-12">
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

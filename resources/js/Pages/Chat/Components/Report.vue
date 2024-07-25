<template>
    <div class="p-10">
        <div v-if="hasSections === false">
            No sections read more <a href="https://docs.larallama.io/docs/reporting" target="_blank">here</a>
            on building reports
        </div>
        <div v-else>
            <div class="flex justify-between items-center">
                <h2
                    v-if="message.report?.reference_collection?.id"
                    class="text-gray-500">Report #{{report?.id}} - Reference Collection Being Used:
                    <Link
                        class="link"
                        :href="route('collections.show', {
                        collection: message.report.reference_collection?.id
                    })">{{ message.report?.reference_collection?.name }}</Link>
                </h2>
                <div  v-show=" report?.id && report?.type === 'rfp'">
                    <a
                    :href="`/reports/${report?.id}/export`" class="btn btn-secondary rounded-none btn-sm">Export</a>
                </div>
            </div>
            <div class="text-lg font-semibold mt-4">Type: {{ report?.type_formatted }}</div>
            <div class="flex justify-start gap-4 items-center mt-1 w-full">

                <div class="text-lg font-semibold">Status</div>
                <div class="badge badge-secondary badge-outline">
                    Section Generation:&nbsp;
                    <div v-if="sectionsStatus === 'Pending'"  class="items-center flex">
                        <span class="loading loading-infinity loading-md"></span>
                    </div>
                    <div v-else>{{ sectionsStatus }}</div>
                </div>
                <div class="badge badge-secondary badge-outline">
                    Strategy Generation::&nbsp;
                    <div v-if="entriesStatus === 'Pending'" class="items-center flex">
                        <span class="loading loading-infinity loading-md"></span>
                    </div>
                    <div v-else>{{ entriesStatus }}</div>
                </div>
            </div>
        </div>
        <div v-if="loadingSections " class="flex justify-start w-full gap-4 items-center mt-4">
            <div class="flex w-full flex-col gap-4 border border-neutral rounded-md p-4">
                <div class="skeleton h-4 w-28"></div>
                <div class="skeleton h-4 w-full"></div>
                <div class="skeleton h-4 w-full"></div>
            </div>
        </div>
        <div v-for="section in report.sections" class="border border-neutral rounded-md p-4 mt-4">
            <Section :section="section"></Section>
        </div>
    </div>
</template>
<script setup>
import {computed, onMounted, onUnmounted, ref} from "vue";
import Clipboard from "@/Components/ClipboardButton.vue";
import { Link, useForm, router } from "@inertiajs/vue3";

import Section from "@/Pages/Chat/Components/Section.vue";

const props = defineProps({
    message: Object
})

const entriesStatus = computed(() => {
    return report.value?.status_entries_generation_formatted ?? "Pending";
})

const sectionsStatus = computed(() => {
    return report.value?.status_sections_generation_formatted ?? "Pending";
})

const hasSections = computed(() => {
    return report.value?.sections?.length > 0;
})

const sections = ref([]);

const report = ref({});

const getSections = () => {
    axios.get(route('api.reports.show', {
        report: props.message.report.id
    })).then(response => {
        report.value = response.data.report;
        loadingSections.value = false;
    }).catch(error => {
        console.log(error)
        loadingSections.value = false;
    })
}

const loadingSections = ref(true);

onMounted(() => {
    if(props.message.report?.id) {
        getSections();
    }

    Echo.private(`collection.chat.reports.${props.message.report?.id}`)
        .listen('.update', (e) => {
            if(loadingSections.value === false) {
                getSections();
            }
        });
});

onUnmounted(() => {
    Echo.leave(`collection.chat.reports.${props.message.report?.id}`);
});
</script>

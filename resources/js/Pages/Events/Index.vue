<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Nav from '@/Pages/Collection/Components/Nav.vue';
import Intro from '@/Components/Intro.vue';
import {useToast} from 'vue-toastification';
import CollectionHeader from "@/Pages/Collection/Components/CollectionHeader.vue";
import Documents from "@/Pages/Collection/Components/Documents.vue";
import CollectionNav from "@/Pages/Collection/Components/CollectionNav.vue";

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import EventShow from "@/Pages/Events/Components/EventShow.vue";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
const toast = useToast();

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    events: {
        type: Object
    },
    today: String
});


const showSlideOut = ref(false);
const event = ref({});

const toggleSlideOut = () => {
    showSlideOut.value = !showSlideOut.value;
};


const handleClicks = (arg) => {
    console.log(arg.event);
    event.value = arg.event;
    toggleSlideOut(arg);
}

const closeSlideOut = () => {
    showSlideOut.value = false;
    event.value = {};
};

const dateClick = (arg) => {
    console.log(arg);
}

const startDate = ref(null);
const endDate = ref(null);
const firstRun = ref(true);
const nextPrevMonth = (arg) => {
    console.log(arg.startStr);
    console.log(arg.endStr);
    startDate.value = arg.startStr;
    endDate.value = arg.endStr;
    if(firstRun.value === true) {
        firstRun.value = false;
        startDate.value = arg.startStr;
        endDate.value = arg.endStr;
        let url = route('collections.events.index', {
            collection: props.collection.data.id,
            start: arg.startStr,
            end: arg.endStr
        });
        router.visit(url, {
            preserveScroll: true,
            preserveState: true,
        });
    } else if(startDate.value !== arg.startStr) {
        startDate.value = arg.startStr;
        endDate.value = arg.endStr;
        let url = route('collections.events.index', {
            collection: props.collection.data.id,
            start: arg.startStr,
            end: arg.endStr
        });
        router.reload({
            only: [props.events],
        });
    }


    // console.log(url);
    // router.visit(url);
}

const calendarOptions = {
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
    },
    initialDate: props.today,
    eventClick: handleClicks,
    dateClick: dateClick,
    datesSet: nextPrevMonth,
    events: props.events.data,
};

</script>

<template>
    <AppLayout title="Events">

        <Nav :collection="collection.data" ></Nav>

        <div class="py-12" v-auto-animate>
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <CollectionHeader
                        :show-edit=false
                        :collection="collection.data"></CollectionHeader>



                    <CollectionNav :collection="collection.data"></CollectionNav>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg p-2">
                    <Intro>
                        Events
                        <template #description>
                            These are events that have been added to the system.
                        </template>
                    </Intro>

                    <FullCalendar :options='calendarOptions' />
                </div>
            </div>
        </div>

            <EventShow
                @closing="closeSlideOut"
                v-if="showSlideOut"
                :open="showSlideOut"
                :event="event"></EventShow>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import dayjs from 'dayjs';
import { useForm, Link } from '@inertiajs/vue3';
import CalendarLayout from "@/Layouts/CalendarLayout.vue";

const props = defineProps({
    collection: Object,
    events: Array,
    startDate: String,
    endDate: String,
    currentMonth: String
});

const viewDate = ref(dayjs(props.currentMonth));

const form = useForm({
    date: viewDate.value.format('YYYY-MM-DD')
});

const units = computed(() => {
    let ranges = [];
    let startOfRange = dayjs(props.startDate);
    let endOfRange = dayjs(props.endDate);

    let currentDate = startOfRange;

    while (currentDate.isBefore(endOfRange) || currentDate.isSame(endOfRange, 'day')) {
        ranges.push(currentDate);
        currentDate = currentDate.add(1, 'day');
    }
    return ranges;
});

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const eventsOnDate = (date) => {
    return props.events.data.filter(event =>
        dayjs(event.start_date).isSame(date, 'day')
    );
};

const shiftMonth = function (amount) {
    viewDate.value = viewDate.value.add(amount, 'month');
    updateCalendar();
};

const reset = function () {
    viewDate.value = dayjs();
    updateCalendar();
};

const updateCalendar = () => {
    form.date = viewDate.value.format('YYYY-MM-DD');
    form.get(route('calendar.show', props.collection.id), {
        preserveState: true,
        preserveScroll: true,
    });
};

const todayLink = computed(() => ({
    href: route('calendar.show', { collection: props.collection.data.id }),
    data: { date: dayjs().format('YYYY-MM-DD') }
}));

const previousMonthLink = computed(() => ({
    href: route('calendar.show', { collection: props.collection.data.id }),
    data: { date: viewDate.value.subtract(1, 'month').format('YYYY-MM-DD') }
}));

const nextMonthLink = computed(() => ({
    href: route('calendar.show', { collection: props.collection.data.id }),
    data: { date: viewDate.value.add(1, 'month').format('YYYY-MM-DD') }
}));

watch(viewDate, (newValue) => {
    form.date = newValue.format('YYYY-MM-DD');
});
</script>

<template>
    <CalendarLayout>
        <div class="flex gap-2 justify-between items-center mb-4">
            <div class="flex gap-2 items-center justify-start">
                <Link v-bind="todayLink" class="btn-outline btn btn-sm rounded-none">Today</Link>
                <Link v-bind="previousMonthLink" class="btn-outline btn btn-sm rounded-none">Previous</Link>
                <Link v-bind="nextMonthLink" class="btn-outline btn btn-sm rounded-none">Next</Link>
            </div>
            <span class="text-3xl">{{ viewDate.format('MMMM YYYY') }}</span>
        </div>

        <div class="grid grid-cols-7 gap-1 mb-2">
            <div v-for="day in weekDays" :key="day" class="text-center font-bold">
                {{ day }}
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <div v-for="date in units" :key="date.format('YYYY-MM-DD')"
                 class="border border-slate-200 p-1 min-h-[100px]"
                 :class="{'bg-gray-100': !date.isSame(viewDate, 'month')}">
                <div class="text-right text-sm">{{ date.format('D') }}</div>
                <div v-for="event in eventsOnDate(date)" :key="event.id"
                     class="text-xs text-white bg-blue-600 p-1 mt-1 rounded">
                    {{ event.title }}
                </div>
            </div>
        </div>
    </CalendarLayout>
</template>

<style scoped>
/* Add any scoped styles here */
</style>

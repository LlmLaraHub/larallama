<script setup>
import { computed, ref } from 'vue';
import dayjs from 'dayjs';
import CalendarLayout from "@/Layouts/CalendarLayout.vue";


const props = defineProps({
    collection: Object,
    events: Array,
    startDate: String
})

const emits = defineEmits(['update:modelValue']);

const viewDate = ref(dayjs(props.startDate));

const units = computed(() => {
    let ranges = [];
    let startOfRange = viewDate.value.startOf('month').add(-1,'day');
    let endOfRange = viewDate.value.endOf('month').add(-1,'day');

    let currentDate = startOfRange;

    while (currentDate.isBefore(endOfRange) || currentDate.isSame(endOfRange)) {
        currentDate = currentDate.add(1, 'day');
        ranges.push(currentDate);
    }
    return ranges;
})

const weekDays = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
]


const daystoPrepend = computed(() => {
    const startOfMonth = viewDate.value.startOf("month");
    const startOfFirstWeek = startOfMonth.startOf("week");
    const daysToFirstDay = startOfMonth.diff(startOfFirstWeek, "day");
    return Array.from(new Array(daysToFirstDay).keys());
})


const shiftMonth = function (amount) {
    viewDate.value = viewDate.value.add(amount, 'month');
}
const reset = function () {
    viewDate.value = dayjs();
}

</script>

<template>
<CalendarLayout>
    <div class="flex gap-2 justify-between items-center">
        <div class="flex gap-2 items-center justify-start">
            <button class="btn-outline btn btn-sm rounded-none"
                    @click="reset()">Today</button>
            <button class="btn-outline btn btn-sm rounded-none"
                    @click="shiftMonth(-1)">Previous</button>
            <button class="btn-outline btn btn-sm rounded-none"
                    @click="shiftMonth(1)">Next</button>
        </div>
        <span class="text-3xl">{{ viewDate.format('MMMM YYYY') }}</span>

    </div>


    <div class="grid grid-cols-7 gap-1">
        <div v-for="d in weekDays"
             class="text-center">
            <div>{{ d }}</div>
        </div>
    </div>

    <div class="grid grid-cols-7">
        <div v-for="p in daystoPrepend"></div>
        <div class="border border-slate-200 flex flex-col h-32"
             v-for="d in units">
            <div class="text-center">{{ d.format('D') }}</div>
        </div>
    </div>
</CalendarLayout>
</template>

<style scoped>

</style>

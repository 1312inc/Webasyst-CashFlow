<script setup lang="ts">
import { computed } from 'vue-demi'

const props = defineProps<{
    date: Date;
    cellHeight: number;
    activeMonth: number;
}>()

const isWeekend = computed(() => {
    const dayOfWeek = props.date.getDay()
    return (dayOfWeek === 6) || (dayOfWeek === 0)
})

const isActiveMonth = computed(() => props.activeMonth === props.date.getMonth())

const isCurrentDay = computed(() => {
    const today = new Date()
    return props.date.getDate() == today.getDate() &&
        props.date.getMonth() == today.getMonth() &&
        props.date.getFullYear() == today.getFullYear()
})

</script>

<template>
    <div class="icg-months-grid-day" :class="{
        'icg-months-grid-day--active-month': isActiveMonth,
        'icg-months-grid-day--weekend': isWeekend,
        'icg-months-grid-day--current': isCurrentDay
    }" :style="{ height: `${props.cellHeight}px` }">
        <slot :date="props.date" :isWeekend="isWeekend" :isActiveMonth="isActiveMonth" :isCurrentDay="isCurrentDay" />
    </div>
</template>

<style>
.icg-months-grid-day {
    position: relative;
    overflow: hidden;
    border-right-style: solid;
    border-bottom-style: solid;
    border-right-width: 1px;
    border-bottom-width: 1px;
    border-color: rgba(209, 213, 219, 0.4);
    box-sizing: border-box;
}

.icg-months-grid-day:nth-child(7n) {
    border-right: 0;
}

.icg-months-grid-day:nth-child(n+36) {
    border-bottom: 0;
}
</style>
<script setup lang="ts">
import dayjs from 'dayjs'
import { defineEmits, computed, ref, watch } from 'vue'

import './style.css'

import { getWeekDaysNames } from './utils'
import { useCalendarScroll } from './useCalendarScroll'

const props = withDefaults(defineProps<{
  firstDayOfWeek?: 0 | 1;
  locale?: string;
  todayLabel?: string;
  items: Record<string, unknown>[];
  fieldWithDate: string;
}>(), {
  firstDayOfWeek: 0,
  locale: 'en-US',
  todayLabel: 'Today'
})

const emit = defineEmits(['changeInterval'])

const wrapperRef = ref<HTMLElement | null>(null)
const containerRef = ref<HTMLElement | null>(null)

const { cellHeight, activeMonth, daysInCalendar } = useCalendarScroll(wrapperRef, containerRef, props.firstDayOfWeek)
const weekDaysNames = getWeekDaysNames(props.locale, props.firstDayOfWeek)

const itemsMap = computed(() => {
  const map: Record<string, unknown[]> = {}
  for (const item of props.items) {
    const fieldContent = item[props.fieldWithDate]
    if (typeof fieldContent === 'string' || typeof fieldContent === 'number') {
      const date = new Date(fieldContent)
      if (!isNaN(date.getTime())) {
        const key = date.toLocaleDateString()
        if (map[key]) {
          map[key].push(item)
        } else {
          map[key] = [item]
        }
      }
    }
  }
  return map
})

watch(daysInCalendar, () => {
  emit('changeInterval', {
    start: daysInCalendar.value[0].timestamp,
    end: daysInCalendar.value[daysInCalendar.value.length - 1].timestamp
  })
}, {
  immediate: true
})

</script>

<template>
  <div class="icg">
    <div class="icg-header">
      <div class="icg-month">
        {{ activeMonth.toDate().toLocaleDateString(props.locale, { year: 'numeric', month: 'long' }).replace('г.', "") }}
      </div>
      <div class="icg-controls">
        <button @click="activeMonth = activeMonth.add(-1, 'M')">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height=".8rem"
            viewBox="0 0 320 512"
          >
            <path
              d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"
            />
          </svg>
        </button>
        <button @click="activeMonth = dayjs()">
          {{ props.todayLabel }}
        </button>
        <button @click="activeMonth = activeMonth.add(1, 'M')">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height=".8rem"
            viewBox="0 0 320 512"
          >
            <path
              d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"
            />
          </svg>
        </button>
      </div>
    </div>
    <div class="icg-weekdays">
      <div
        v-for="weekday, i in weekDaysNames"
        :key="weekday"
        class="icg-weekdays__cell"
        :class="{ 'icg-weekdays__cell--weekend': i === (props.firstDayOfWeek ? 5 : 0) || i === 6 }"
      >
        {{ weekday }}
      </div>
    </div>

    <div
      ref="wrapperRef"
      class="icg-wrapper"
    >
      <div
        ref="containerRef"
        class="icg-months-grid"
      >
        <div
          v-for="date in daysInCalendar"
          :key="date.timestamp"
          class="icg-months-grid-day"
          :class="{
            'icg-months-grid-day--active-month': date.isCurrentMonth,
            'icg-months-grid-day--first-day': date.isFirstDay,
            'icg-months-grid-day--weekend': date.isWeekend,
            'icg-months-grid-day--current': date.isCurrent
          }"
          :style="{ height: `${cellHeight}px` }"
        >
          <slot v-bind="{ date, items: itemsMap[date.localeDate] }" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import dayjs from 'dayjs'
import { defineEmits, onMounted } from 'vue'
import { computed, nextTick, ref, watch } from 'vue-demi'
import { useElementSize, useScroll } from '@vueuse/core'
import InfiniteCalendarGridDay from './InfiniteCalendarGridDay.vue'

const props = withDefaults(defineProps<{
  firstDayOfWeek?: 0 | 1;
  locale?: string;
  todayLabel?: string;
}>(), {
  firstDayOfWeek: 0,
  locale: 'en-US',
  todayLabel: 'Today'
})

const emit = defineEmits(['changed'])

const weekDays = new Array(7).fill(undefined).map((_e, i) => {
  const curDate = new Date().getDate()
  const curDay = new Date().getDay()
  return new Date(new Date().setDate((curDate - curDay) + i)).toLocaleDateString(props.locale, { weekday: 'short' })
})

const containerRef = ref<HTMLElement | null>(null)
const { height } = useElementSize(containerRef)
const cellHeight = computed(() => Math.ceil(height.value / 6))
const { y, arrivedState } = useScroll(containerRef)
const weekDaysNames = computed(() => {
  if (!!props.firstDayOfWeek) {
    const t = [...weekDays]
    t.push(t.shift() as string)
    return t
  } else {
    return weekDays
  }
})

const activeMonth = ref(dayjs())
const activeMonthOffset = computed(() => {
  const offset = activeMonth.value.startOf('M').get('d') + (props.firstDayOfWeek && -1)
  return offset < 0 ? 7 + offset : offset
})
const activeMonthRows = computed(() => Math.ceil((activeMonth.value.daysInMonth() + activeMonthOffset.value) / 7))
const prevMonthRows = computed(() => Math.ceil((activeMonth.value.add(-1, 'month').daysInMonth() - activeMonthOffset.value) / 7))
const nextMonthRows = computed(() => {
  let rows = Math.ceil(activeMonth.value.add(1, 'month').daysInMonth() / 7)
  if (activeMonth.value.endOf('M').get('d') === (props.firstDayOfWeek ? 0 : 6) && activeMonthRows.value === 5) {
    rows++
  }
  return rows
})
const startDate = computed(() => activeMonth.value.startOf('M').add(-(prevMonthRows.value * 7 + activeMonthOffset.value), 'day'))
const daysCount = computed(() => new Array((activeMonthRows.value + prevMonthRows.value + nextMonthRows.value) * 7).fill(undefined).map((_e, i) => startDate.value.add(i, 'day').toDate()))
const daysCountSlices = computed(() => {
  const arr1 = daysCount.value.slice(0, prevMonthRows.value * 7)
  const arr2 = daysCount.value.slice(prevMonthRows.value * 7, prevMonthRows.value * 7 + activeMonthRows.value * 7)
  const arr3 = daysCount.value.slice(prevMonthRows.value * 7 + activeMonthRows.value * 7, prevMonthRows.value * 7 + activeMonthRows.value * 7 + nextMonthRows.value * 7)

  return {
    [arr1[0].getTime()]: arr1,
    [arr2[0].getTime()]: arr2,
    [arr3[0].getTime()]: arr3
  }
})

const unw = watch(height, async (height) => {
  if (height) {
    await nextTick()
    gotoCurrentMonth()
    unw()
  }
})

onMounted(async () => {
  await nextTick()
  watch(arrivedState, ({ top, bottom }) => {
    if (top || bottom) {
      activeMonth.value = activeMonth.value.add(top ? -1 : 1, 'month')
    }
  })
})

watch(activeMonth, async () => {
  await nextTick()
  gotoCurrentMonth()
  emit('changed', activeMonth.value.toDate())
})

function gotoCurrentMonth() {
  y.value = cellHeight.value * prevMonthRows.value
}
</script>

<template>
  <div class="icg" :firstdayofweek="props.firstDayOfWeek">
    <div class="icg-header">
      <div class="icg-month">
        {{ activeMonth.toDate().toLocaleDateString(props.locale, { year: 'numeric', month: 'long' }).replace('г.', "") }}
      </div>
      <div class="icg-controls">
        <button @click="activeMonth = activeMonth.add(-1, 'month')">
          <svg xmlns="http://www.w3.org/2000/svg" height=".8rem" viewBox="0 0 320 512">
            <path
              d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z" />
          </svg>
        </button>
        <button @click="activeMonth = dayjs()">{{ props.todayLabel }}</button>
        <button @click="activeMonth = activeMonth.add(1, 'month')">
          <svg xmlns="http://www.w3.org/2000/svg" height=".8rem" viewBox="0 0 320 512">
            <path
              d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z" />
          </svg>
        </button>
      </div>
    </div>
    <div class="icg-weekdays">
      <div v-for="weekday, i in weekDaysNames" :key="weekday" class="icg-weekdays__cell"
        :class="{ 'icg-weekdays__cell--weekend': i === (props.firstDayOfWeek ? 5 : 0) || i === 6 }">
        {{ weekday }}
      </div>
    </div>
    <div ref="containerRef" class="icg-months-grid">
      <div v-for="array, key, i in daysCountSlices" :key="key"
        :class="['icg-months-grid-month', {'icg-months-grid-month--selected': i === 1}]">
        <InfiniteCalendarGridDay v-for="date in array" :key="date.getTime()" :cell-height="cellHeight" :activeMonth="activeMonth.month()"
          :date="date">
          <template #default="props">
            <slot v-bind="props"></slot>
          </template>
        </InfiniteCalendarGridDay>
      </div>
    </div>
  </div>
</template>

<style>
.icg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.icg-header {
  flex: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
}

.icg-month {
  text-transform: capitalize;
}

.icg-controls {
  display: flex;
}

.icg-weekdays {
  flex: none;
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.icg-weekdays__cell {
  text-align: right;
  padding-right: 1rem;
  padding-bottom: 1rem;
  border-bottom-style: solid;
  border-color: rgba(209, 213, 219, 0.4);
  border-bottom-width: 1px;
  box-sizing: border-box;
}

.icg-months-grid {
  flex: 1 1 auto;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  overflow-y: scroll;
  scroll-snap-type: y mandatory;
  scrollbar-width: none;
}

.icg-months-grid::-webkit-scrollbar {
  display: none;
}

.icg-months-grid-month {
  scroll-snap-stop: always;
  scroll-snap-align: start;
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.icg-months-grid-month:last-of-type {
  scroll-snap-align: end;
}
</style>
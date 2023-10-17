<script setup lang="ts">
import dayjs from 'dayjs'
import { defineEmits } from 'vue'
import { computed, nextTick, ref, watch } from 'vue-demi'
import { useElementSize, useScroll } from '@vueuse/core'
import InfiniteCalendarGridDay from './InfiniteCalendarGridDay.vue'

const props = withDefaults(defineProps<{
  weekDays?: string[];
  firstDayOfWeek?: 0 | 1;
}>(), {
  weekDays: () => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
  firstDayOfWeek: 0
})

const emit = defineEmits(['changed'])

const containerRef = ref<HTMLElement | null>(null)
const offsetContainerRef = ref<HTMLElement | null>(null)
const { height } = useElementSize(containerRef)
const cellHeight = computed(() => height.value / 6)
const { y, arrivedState } = useScroll(containerRef)
const weekDaysNames = computed(() => {
  if (!!props.firstDayOfWeek) {
    const t = [...props.weekDays]
    t.push(t.shift() as string)
    return t
  } else {
    return props.weekDays
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

const unw = watch(height, async (height) => {
  if (height) {
    await nextTick()
    gotoCurrentMonth()
    unw()
  }
})

watch(arrivedState, async ({ top, bottom }) => {
  if (top || bottom) {
    activeMonth.value = activeMonth.value.add(top ? -1 : 1, 'month')
    await nextTick()
    gotoCurrentMonth()
    emit('changed', activeMonth.value.month())
  }
})

function gotoCurrentMonth() {
  if (offsetContainerRef.value) {
    y.value = offsetContainerRef.value.clientHeight
  }
}
</script>

<template>
  <div class="icg" :firstdayofweek="props.firstDayOfWeek">
    <div class="icg-month">
      {{ activeMonth.format('MMMM') }} {{ activeMonth.format('YYYY') }}
    </div>
    <div class="icg-weekdays">
      <div v-for="weekday in weekDaysNames" :key="weekday" class="icg-weekdays__cell">
        {{ weekday }}
      </div>
    </div>
    <div ref="containerRef" class="icg-months-grid">
      <div ref="offsetContainerRef" class="icg-months-grid-month">
        <InfiniteCalendarGridDay v-for="date in daysCount.slice(0, prevMonthRows * 7)" :key="date.getTime()"
          :cell-height="cellHeight" :date="date">
          <template #default="props">
            <slot v-bind="props"></slot>
          </template>
        </InfiniteCalendarGridDay>
      </div>
      <div class="icg-months-grid-month">
        <InfiniteCalendarGridDay
          v-for="date in daysCount.slice(prevMonthRows * 7, prevMonthRows * 7 + activeMonthRows * 7)"
          :key="date.getTime()" :cell-height="cellHeight" :date="date">
          <template #default="props">
            <slot v-bind="props"></slot>
          </template>
          </InfiniteCalendarGridDay>
      </div>
      <div class="icg-months-grid-month">
        <InfiniteCalendarGridDay
          v-for="date in daysCount.slice(prevMonthRows * 7 + activeMonthRows * 7, prevMonthRows * 7 + activeMonthRows * 7 + nextMonthRows * 7)"
          :key="date.getTime()" :cell-height="cellHeight" :date="date">
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

.icg-month {
  flex: none;
  padding: 1rem;
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
  overflow-y: scroll;
  overflow-x: hidden;
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
</style>
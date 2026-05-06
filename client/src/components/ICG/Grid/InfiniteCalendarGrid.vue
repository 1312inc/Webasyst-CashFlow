<script setup>
import dayjs from 'dayjs'
import { defineEmits, computed, ref, watch } from 'vue'

import './style.css'

import { getWeekDaysNames } from './utils'
import { useCalendarScroll } from './useCalendarScroll'

const props = defineProps({
  firstDayOfWeek: {
    type: Number,
    default: 0
  },
  locale: {
    type: String,
    default: 'en-US'
  },
  todayLabel: {
    type: String,
    default: 'Today'
  },
  items: {
    type: Array,
    default: () => []
  },
  fieldWithDate: {
    type: String,
    default: ''
  },
  mode: {
    type: String,
    default: 'summary'
  }
})

const emit = defineEmits(['changeInterval', 'changeMode'])

const wrapperRef = ref(null)
const containerRef = ref(null)
const selectedCurrency = ref('')

const { cellHeight, activeMonth, daysInCalendar } = useCalendarScroll(wrapperRef, containerRef, props.firstDayOfWeek)
const weekDaysNames = getWeekDaysNames(props.locale, props.firstDayOfWeek)

const currencies = computed(() => {
  const list = []
  for (const item of props.items) {
    const record = item
    if (typeof record.currency === 'string' && !list.includes(record.currency)) {
      list.push(record.currency)
    }
    const nested = record.data
    if (Array.isArray(nested)) {
      for (const sub of nested) {
        const nestedRecord = sub
        if (typeof nestedRecord.currency === 'string' && !list.includes(nestedRecord.currency)) {
          list.push(nestedRecord.currency)
        }
      }
    }
  }
  return list
})

watch(currencies, (value) => {
  if (!value.includes(selectedCurrency.value)) {
    selectedCurrency.value = value[0] || ''
  }
}, { immediate: true })

const filteredItems = computed(() => {
  if (!selectedCurrency.value) return props.items
  const result = []
  for (const item of props.items) {
    const record = item

    if (typeof record.currency === 'string' && record.currency === selectedCurrency.value) {
      result.push(item)
      continue
    }

    if (Array.isArray(record.data)) {
      const filteredData = record.data
        .filter(sub => sub.currency === selectedCurrency.value)
      if (filteredData.length) {
        result.push({
          ...record,
          data: filteredData
        })
      }
    }
  }
  return result
})

const itemsMap = computed(() => {
  const map = {}
  for (const item of filteredItems.value) {
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
  const middleDays = daysInCalendar.value.slice(42, 84)
  emit('changeInterval', {
    start: middleDays[0].timestamp,
    end: middleDays[middleDays.length - 1].timestamp
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

      <div class="flexbox vertical-mobile">
        <div class="toggle custom-mr-24 custom-mr-0-mobile custom-mb-8-mobile">
          <span
            :class="{ 'selected': props.mode === 'summary' }"
            @click="emit('changeMode', 'summary')"
          >Итого</span>
          <span
            :class="{ 'selected': props.mode === 'operations' }"
            @click="emit('changeMode', 'operations')"
          >Операции</span>
        </div>

        <div
          v-if="currencies.length > 1"
          class="toggle custom-mr-24 custom-mr-0-mobile custom-mb-8-mobile"
        >
          <span
            v-for="currency in currencies"
            :key="currency"
            :class="{ selected: selectedCurrency === currency }"
            @click="selectedCurrency = currency"
          >
            {{ currency }}
          </span>
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

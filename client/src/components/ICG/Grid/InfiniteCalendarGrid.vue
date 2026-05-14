<script setup>
import dayjs from 'dayjs'
import { defineEmits, computed, ref, watch } from 'vue'

import currencyIcons from '@/utils/currencyIcons'
import DropdownWaFloating from '@/components/Inputs/DropdownWaFloating.vue'
import './style.css'

import { getWeekDaysNames } from './utils'
import { useCalendarScroll } from './useCalendarScroll'
import store from '@/store'
import { i18n } from '@/plugins/locale'

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

const emit = defineEmits(['changeInterval', 'changeMode', 'changeChartFilter'])

const wrapperRef = ref(null)
const containerRef = ref(null)

const { cellHeight, activeMonth, daysInCalendar } = useCalendarScroll(wrapperRef, containerRef, props.firstDayOfWeek)
const weekDaysNames = getWeekDaysNames(props.locale, props.firstDayOfWeek)

const accounts = computed(() => store.state.account.accounts)
const incomeCategories = computed(() => store.getters['category/getByType']('income'))
const expenseCategories = computed(() => store.getters['category/getByType']('expense'))
const currenciesForFiltering = computed(() => store.getters['account/currenciesInAccounts'] || [])

const activeChartFilter = ref({
  type: 'calendar',
  id: null,
  label: ''
})

const filterButtonLabel = computed(() => {
  if (activeChartFilter.value.type === 'calendar') {
    return i18n.t('calendarGrid.filterAllOperations')
  }
  return activeChartFilter.value.label || i18n.t('calendarGrid.filterAllOperations')
})

function isChartFilterActive (type, id = undefined) {
  const a = activeChartFilter.value
  if (type === 'calendar') return a.type === 'calendar'
  return a.type === type && a.id === id
}

function applyChartFilter (close, apiFilter, next) {
  activeChartFilter.value = next
  emit('changeChartFilter', apiFilter)
  if (typeof close === 'function') close()
}

function selectCalendarFilter (close) {
  applyChartFilter(close, 'calendar', {
    type: 'calendar',
    id: null,
    label: ''
  })
}

function selectCurrencyFilter (close, code) {
  applyChartFilter(close, `currency/${code}`, {
    type: 'currency',
    id: code,
    label: code
  })
}

function selectAccountFilter (close, account) {
  applyChartFilter(close, `account/${account.id}`, {
    type: 'account',
    id: account.id,
    label: account.name
  })
}

function selectCategoryFilter (close, category) {
  applyChartFilter(close, `category/${category.id}`, {
    type: 'category',
    id: category.id,
    label: category.name
  })
}

const itemsMap = computed(() => {
  const map = {}
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

/** Max of |income|, |expense|, |profit| per day, then max across days (active month). Largest circle = 32px. */
const monthChartMaxAbs = computed(() => {
  const byDay = {}
  for (const item of props.items) {
    const fieldContent = item[props.fieldWithDate]
    if (!fieldContent) continue
    const date = dayjs(fieldContent)
    if (!date.isValid() || !date.isSame(activeMonth.value, 'month')) continue

    const key = date.format('YYYY-MM-DD')
    if (!byDay[key]) {
      byDay[key] = { income: 0, expense: 0, profit: 0 }
    }
    if (Array.isArray(item.data)) {
      for (const row of item.data) {
        byDay[key].income += Number(row.amountIncome) || 0
        byDay[key].expense += Number(row.amountExpense) || 0
        byDay[key].profit += Number(row.amountProfit) || 0
      }
    }
  }

  let maxAbs = 0
  for (const t of Object.values(byDay)) {
    const dayPeak = Math.max(
      Math.abs(t.income),
      Math.abs(t.expense),
      Math.abs(t.profit)
    )
    if (dayPeak > maxAbs) maxAbs = dayPeak
  }
  return maxAbs
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
      <div
        class="flexbox vertical-mobile space-16"
        style="align-items: start;"
      >
        <div class="icg-month nowrap">
          {{ activeMonth.toDate().toLocaleDateString(props.locale, { year: 'numeric', month: 'long' }).replace('г.', "") }}
        </div>

        <DropdownWaFloating v-if="props.mode === 'summary'">
          <template #toggler>
            <button class="button light-gray large nowrap">
              <strong>{{ filterButtonLabel }}</strong>
              <span class="icon text-light-gray">
                <i class="fas fa-chevron-down custom-mt-2 custom-ml-4 fa-xs" />
              </span>
            </button>
          </template>

          <template #default="{ close }">
            <div>
              <ul class="menu custom-mt-0">
                <li
                  :class="{ 'selected': isChartFilterActive('calendar') }"
                >
                  <a
                    href="#"
                    @click.prevent="selectCalendarFilter(close)"
                  >{{ $t('calendarGrid.filterAllOperations') }}</a>
                </li>
              </ul>
              <div class="heading custom-ml-12">
                {{ $t('currency') }}
              </div>
              <ul class="menu">
                <li
                  v-for="currency in currenciesForFiltering"
                  :key="'cur-' + currency"
                  :class="{ 'selected': isChartFilterActive('currency', currency) }"
                >
                  <a
                    href="#"
                    @click.prevent="selectCurrencyFilter(close, currency)"
                  >{{ currency }}</a>
                </li>
              </ul>
              <div class="heading custom-ml-12">
                {{ $t('account') }}
              </div>
              <ul class="menu">
                <li
                  v-for="account in accounts"
                  :key="'acc-' + account.id"
                  :class="{ 'selected': isChartFilterActive('account', account.id) }"
                >
                  <a
                    href="#"
                    @click.prevent="selectAccountFilter(close, account)"
                  ><span class="icon">
                     <img
                       v-if="$helper.isValidHttpUrl(account.icon)"
                       :src="account.icon"
                       alt=""
                       class="size-20"
                       :class="{ 'userpic': account.accountable_contact_id }"
                     >
                     <span v-else>
                       <i :class="`fas ${currencyIcons[account.currency] || currencyIcons.default}`" />
                     </span>
                   </span>
                    <span>{{ account.name }}</span></a>
                </li>
              </ul>
              <div class="heading custom-ml-12">
                {{ $t('income') }}
              </div>
              <ul class="menu">
                <li
                  v-for="category in incomeCategories"
                  :key="'inc-' + category.id"
                  :class="{ 'selected': isChartFilterActive('category', category.id) }"
                >
                  <a
                    href="#"
                    @click.prevent="selectCategoryFilter(close, category)"
                  >
                    <span
                      v-if="category.glyph"
                      :key="category.color"
                      class="icon"
                    >
                      <i
                        :class="category.glyph"
                        :style="`color:${category.color};`"
                      />
                    </span>
                    <span
                      v-else
                      class="icon"
                    >
                      <i
                        class="rounded"
                        :style="`background-color:${category.color};`"
                      />
                    </span>
                    {{ category.name }}</a>
                </li>
              </ul>
              <div class="heading custom-ml-12">
                {{ $t('expense') }}
              </div>
              <ul class="menu">
                <li
                  v-for="category in expenseCategories"
                  :key="'exp-' + category.id"
                  :class="{ 'selected': isChartFilterActive('category', category.id) }"
                >
                  <a
                    href="#"
                    @click.prevent="selectCategoryFilter(close, category)"
                  >
                    <span
                      v-if="category.glyph"
                      :key="category.color"
                      class="icon"
                    >
                      <i
                        :class="category.glyph"
                        :style="`color:${category.color};`"
                      />
                    </span>
                    <span
                      v-else
                      class="icon"
                    >
                      <i
                        class="rounded"
                        :style="`background-color:${category.color};`"
                      />
                    </span>
                    {{ category.name }}</a>
                </li>
              </ul>
            </div>
          </template>
        </DropdownWaFloating>
      </div>

      <div class="icg-controls">
        <div class="toggle custom-mr-16">
          <span
            :class="{ 'selected': props.mode === 'summary' }"
            @click="emit('changeMode', 'summary')"
          >{{ $t('calendarGrid.modeSummary') }}</span>
          <span
            :class="{ 'selected': props.mode === 'operations' }"
            @click="emit('changeMode', 'operations')"
          >{{ $t('calendarGrid.modeOperations') }}</span>
        </div>
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
          <slot v-bind="{ date, items: itemsMap[date.localeDate], monthChartMaxAbs }" />
        </div>
      </div>
    </div>
  </div>
</template>

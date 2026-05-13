<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <InfiniteCalendarGrid
    :first-day-of-week="1"
    :locale="locale.replace('_', '-')"
    :today-label="$t('today')"
    :items="dataDays"
    field-with-date="date"
    :mode="mode"
    @changeInterval="handleMonthChange"
    @changeMode="handleChangeMode"
    @changeChartFilter="onChartFilterChange"
  >
    <template #default="{ date, items, monthChartMaxAbs }">
      <InfiniteCalendarGridDaySlot
        :date="new Date(date.timestamp)"
        :data="items"
        :mode="mode"
        :is-current-month="date.isCurrentMonth"
        :month-chart-max-abs="monthChartMaxAbs"
      />
    </template>
  </InfiniteCalendarGrid>
</template>

<script setup>
import InfiniteCalendarGrid from '../components/ICG/Grid/InfiniteCalendarGrid.vue'
import InfiniteCalendarGridDaySlot from '../components/ICG/InfiniteCalendarGridDaySlot.vue'
import api from '@/plugins/api'
import dayjs from 'dayjs'
import { ref } from 'vue'
import { locale } from '@/plugins/locale'
import { useTitle } from '@vueuse/core'
import { i18n } from '../plugins/locale'
import store from '@/store'

const mode = ref('summary')
const dataDays = ref([])
const chartFilterParam = ref('calendar')

let startDate
let endDate
let controller

useTitle(`${i18n.t('calendar')} – ${window.appState?.accountName || ''}`)

function onChartFilterChange (filter) {
  chartFilterParam.value = filter
  if (startDate != null && endDate != null) {
    handleMonthChange({ start: startDate, end: endDate })
  }
}

try {
  const storedMode = localStorage.getItem('cashCalendarMode')
  if (storedMode && ['summary', 'operations'].includes(storedMode)) { mode.value = storedMode }
} catch {}

const handleMonthChange = ({ start, end }) => {
  startDate = start
  endDate = end

  if (controller) {
    controller.abort()
  }
  controller = new AbortController()

  if (mode.value === 'summary') {
    api.get('cash.aggregate.getChartData', {
      signal: controller.signal,
      params: {
        from: dayjs(start).format('YYYY-MM-DD'),
        to: dayjs(end).format('YYYY-MM-DD'),
        group_by: 'day',
        filter: chartFilterParam.value,
        reverse: 1
      }
    })
      .then(({ data }) => {
        // [
        //   {
        //       "currency": "KRW",
        //       "data": [
        //           {
        //               "amountIncome": 0,
        //               "amountExpense": 0,
        //               "amountProfit": 0,
        //               "countIncome": 0,
        //               "countExpense": 0,
        //               "countProfit": 0,
        //               "balance": null,
        //               "period": "2026-06-01"
        //           },
        //         ]
        //   }
        // ]
        const map = new Map()

        for (const currencyData of data) {
          const currency = currencyData.currency

          for (const c of currencyData.data) {
            if (c.amountIncome || c.amountExpense || c.amountProfit) {
              const obj = {
                ...c,
                currency
              }

              const prev = map.get(c.period) || []
              map.set(c.period, [...prev, obj])
            }
          }
        }

        dataDays.value = [...map.entries()].map(([date, data]) => ({ date, data }))
      })
      .catch((e) => e)
  } else if (mode.value === 'operations') {
    api.get('cash.transaction.getList', {
      signal: controller.signal,
      params: {
        from: dayjs(start).format('YYYY-MM-DD'),
        to: dayjs(end).format('YYYY-MM-DD'),
        reverse: 1
      }
    })
      .then(({ data }) => {
        dataDays.value = data.data
      })
      .catch((e) => e)
  }
}

const handleChangeMode = (newMode) => {
  mode.value = newMode
  dataDays.value = []
  handleMonthChange({ start: startDate, end: endDate })
  localStorage.setItem('cashCalendarMode', newMode)
}

store.subscribeAction({
  after: (action) => {
    if (action.type === 'transaction/update' || action.type === 'transaction/delete') {
      handleMonthChange({ start: startDate, end: endDate })
    }
  }
})

</script>

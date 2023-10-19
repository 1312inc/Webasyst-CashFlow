<template>
  <InfiniteCalendarGrid
    :first-day-of-week="1"
    :locale="locale.replace('_', '-')"
    :today-label="$t('today')"
    @changed="handleMonthChange"
  >
    <template #default="{ date }">
      <InfiniteCalendarGridDaySlot
        :date="date"
        :data="dayWithactions(date)"
      />
    </template>
  </InfiniteCalendarGrid>
</template>

<script setup>
import InfiniteCalendarGrid from '../components/ICG/InfiniteCalendarGrid.vue'
import InfiniteCalendarGridDaySlot from '../components/ICG/InfiniteCalendarGridDaySlot.vue'
import api from '@/plugins/api'
import dayjs from 'dayjs'
import { onMounted } from 'vue'
import { ref } from 'vue-demi'
import { locale } from '@/plugins/locale'

const dataDays = ref({})

onMounted(() => {
  handleMonthChange()
})

function dayWithactions (date) {
  return dataDays.value[dayjs(date).format('YYYY-MM-DD')]
}

async function handleMonthChange (e) {
  await api.get('cash.transaction.getList', {
    params: {
      from: dayjs().add(-1, 'month').startOf('M').format('YYYY-MM-DD'),
      to: dayjs().add(1, 'month').endOf('M').format('YYYY-MM-DD')
    }
  }).then(({ data }) => {
    const datesWithActions = data.data.reduce((acc, e) => {
      if (!acc[e.date]) acc[e.date] = []
      acc[e.date].push(e)
      return acc
    }, {})

    for (const date in datesWithActions) {
      const income = reduceForCurrencies(datesWithActions[date].filter(e => e.amount > 0))
      const outcome = reduceForCurrencies(datesWithActions[date].filter(e => e.amount < 0))

      datesWithActions[date] = {
        income: Object.keys(income).length ? income : null,
        outcome: Object.keys(outcome).length ? outcome : null
      }
    }

    dataDays.value = datesWithActions
  })

  function reduceForCurrencies (array) {
    return array.reduce((acc, e) => {
      acc[e.account_id] ??= {
        amount: 0,
        count: 0
      }
      acc[e.account_id].amount += e.amount
      acc[e.account_id].count++
      return acc
    }, {})
  }
}

</script>

<style lang="scss">

.icg-header {
  align-items: start;
  gap: 1rem;
}

.icg-controls {
    button {

      background-color: var(--background-color-btn-light-gray);
      color: var(--text-color-input);
      box-shadow: none;

        svg {
            fill: var(--text-color-input);
        }
    }
}

.icg-weekdays__cell--weekend {
    color: var(--text-color-hint);
}

.icg-month {
    font-size: 2rem;
    color: var(--text-color-strongest);
    line-height: 1.2em;
    font-weight: bold;
}

.icg-months-grid-day {
    color: var(--text-color-hint);
    background-color: rgba(0, 20, 80, 0.01);
}

.icg-months-grid-day--active-month {
  background-color: var(--background-color-blank);
}

.icg-months-grid-day--weekend {
  background-color: rgba(0, 20, 80, 0.03);
}

</style>

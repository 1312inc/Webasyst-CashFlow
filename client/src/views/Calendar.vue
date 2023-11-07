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
        :data="dataDays.filter(t => t.date === dayjs(date).format('YYYY-MM-DD'))"
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
import store from '@/store'

const dataDays = ref([])
let curDate = new Date()

onMounted(() => {
  handleMonthChange(curDate)
})

async function handleMonthChange (date) {
  curDate = date
  const curDayjs = dayjs(curDate)

  await api.get('cash.transaction.getList', {
    params: {
      from: curDayjs.add(-2, 'month').startOf('M').format('YYYY-MM-DD'),
      to: curDayjs.add(2, 'month').endOf('M').format('YYYY-MM-DD')
    }
  }).then(({ data }) => {
    dataDays.value = data.data
  })
}

store.subscribeAction({
  after: (action) => {
    if (action.type === 'transaction/update') {
      handleMonthChange(curDate)
    }
  }
})

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

.icg-weekdays__cell,
.icg-months-grid-day {
  border-color: var(--border-color-soft);
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

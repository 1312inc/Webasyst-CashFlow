<template>
  <InfiniteCalendarGrid
    :first-day-of-week="firstDayOfWeek"
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
import { ref, computed } from 'vue'
import { locale } from '@/plugins/locale'
import store from '@/store'

const firstDayOfWeek = ref(1)
const dataDays = ref([])
const curDate = ref(dayjs())

const activeMonthOffset = computed(() => {
  const offset = curDate.value.startOf('M').get('d') + (firstDayOfWeek.value && -1)
  return offset < 0 ? 7 + offset : offset
})

handleMonthChange(new Date())

async function handleMonthChange (date) {
  curDate.value = dayjs(date)

  await api.get('cash.transaction.getList', {
    params: {
      from: curDate.value.startOf('M').add(-activeMonthOffset.value, 'day').format('YYYY-MM-DD'),
      to: curDate.value.endOf('M').add(42 - activeMonthOffset.value - curDate.value.daysInMonth(), 'day').format('YYYY-MM-DD'),
      reverse: 1
    }
  }).then(({ data }) => {
    dataDays.value = data.data
  })
}

store.subscribeAction({
  after: (action) => {
    if (action.type === 'transaction/update' || action.type === 'transaction/delete') {
      handleMonthChange(curDate.value)
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

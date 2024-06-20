<template>
  <InfiniteCalendarGrid
    :first-day-of-week="1"
    :locale="locale.replace('_', '-')"
    :today-label="$t('today')"
    :items="dataDays"
    field-with-date="date"
    @changeInterval="handleMonthChange"
  >
    <template #default="{ date, items }">
      <InfiniteCalendarGridDaySlot
        :date="new Date(date.timestamp)"
        :data="items"
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
import store from '@/store'

const dataDays = ref([])
let startDate
let endDate
let controller

const handleMonthChange = ({ start, end }) => {
  startDate = start
  endDate = end

  if (controller) {
    controller.abort()
  }
  controller = new AbortController()

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

store.subscribeAction({
  after: (action) => {
    if (action.type === 'transaction/update' || action.type === 'transaction/delete') {
      handleMonthChange({ start: startDate, end: endDate })
    }
  }
})

</script>

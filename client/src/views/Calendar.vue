<template>
  <InfiniteCalendarGrid
    :first-day-of-week="1"
    @changed="handleMonthChange"
  >
    <template #default="{ date, isCurrentDay }">
      <div
        class="absolute align-right custom-p-8"
        style="width: 100%; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;"
      >
        <div class="gray">
          {{ date.getDate() }}
        </div>

        <div
          v-if="dayWithactions(date)"
          style="display: flex; flex-direction: column; row-gap: 4px;"
        >
          <template v-if="dayWithactions(date).income">
            <div
              v-for="key, accountId in dayWithactions(date).income"
              :key="accountId"
              class="text-green align-right"
            >
              +{{ key.amount }} <span
                v-if="key.count > 1"
                class="badge green"
              >{{ key.count }}</span>
              {{ getCurrency(+accountId) }}
            </div>
          </template>
          <template v-if="dayWithactions(date).outcome">
            <div
              v-for="key, accountId in dayWithactions(date).outcome"
              :key="accountId"
              class="text-red align-right"
            >
              {{ key.amount }} <span
                v-if="key.count > 1"
                class="badge"
              >{{ key.count }}</span>
              {{ getCurrency(+accountId) }}
            </div>
          </template>
        </div>
      </div>
    </template>
  </InfiniteCalendarGrid>
</template>

<script setup>
import InfiniteCalendarGrid from '../components/ICG/InfiniteCalendarGrid.vue'
import api from '@/plugins/api'
import dayjs from 'dayjs'
import { computed, onMounted } from 'vue'
import { ref } from 'vue-demi'

const dataDays = ref({})

onMounted(() => {
  handleMonthChange()
})

const dayWithactions = computed(() => (date) => dataDays.value[dayjs(date).format('YYYY-MM-DD')])

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

<script>
export default {
  methods: {
    getCurrency (entityId) {
      return this.$store.getters['account/getById'](entityId)?.currency
    }
  }

}
</script>

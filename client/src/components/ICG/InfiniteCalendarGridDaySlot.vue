<script setup>
import { locale } from '@/plugins/locale'

const props = defineProps(['date', 'data'])

function getMonthShort (date) {
  try {
    return date.toLocaleString(locale.replace('_', '-'), { month: 'short' })
  } catch (_e) { }
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

<template>
  <div
    class="absolute align-right custom-p-8"
    style="width: 100%; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer;"
    @click="() => $router.push(`/date/${$moment(date).format('YYYY-MM-DD')}/`)"
  >
    <div class="day">
      {{ date.getDate() }} <span v-if="date.getDate() === 1">{{ getMonthShort(date) }}</span>
    </div>

    <div
      v-if="props.data"
      class="data"
    >
      <div
        v-for="operation, typeOfOperation in props.data"
        :key="typeOfOperation"
      >
        <div
          v-for="operationData, accountId in operation"
          :key="accountId"
          class="align-right custom-mt-4"
          :class="{
            'text-red': typeOfOperation === 'outcome',
            'text-green': typeOfOperation === 'income'
          }"
        >
          {{ typeOfOperation === 'income' ? '+' : '' }}{{ operationData.amount }} <span
            v-if="operationData.count > 1"
            class="badge"
            :class="{
              'green': typeOfOperation === 'income'
            }"
          >{{ operationData.count }}</span>
          {{ getCurrency(+accountId) }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.icg-months-grid-day--active-month .day {
    color: var(--text-color-strong);
}

.icg-months-grid-day--current .day {
    background-color: var(--red);
    color: var(--white);
    display: flex;
    justify-content: center;
    align-items: center;
    width: 2rem;
    height: 2rem;
    border-radius: 100%;
    margin-left: auto;
    transform: translateX(.4rem) translateY(-.4rem);
}

@media screen and (max-width: 760px) {

  .data {
    font-size: 0.8rem;
  }

}

</style>

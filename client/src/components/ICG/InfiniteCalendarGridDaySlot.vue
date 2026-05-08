<script setup>
import { computed, onMounted, ref } from 'vue'
import { locale } from '@/plugins/locale'
import { emitter } from '@/plugins/eventBus'
import InfiniteCalendarGridDaySlotItem from './InfiniteCalendarGridDaySlotItem.vue'
import InfiniteCalendarGridDaySlotItemSummary from './InfiniteCalendarGridDaySlotItemSummary.vue'
import dayjs from 'dayjs'
import { useRouter } from 'vue-router/composables'
import { moment } from '@/plugins/numeralMoment.js'
import store from '@/store'

const props = defineProps({
  date: {
    type: [String, Date],
    default: ''
  },
  data: {
    type: Array,
    default: () => []
  },
  mode: {
    type: String,
    default: 'summary'
  },
  isCurrentMonth: {
    type: Boolean,
    default: false
  },
  monthChartMaxAbs: {
    type: Number,
    default: 0
  }
})

const CHART_CIRCLE_MAX_PX = 40
const CHART_CIRCLE_MIN_PX = 5

const router = useRouter()
const dayRef = ref()
const chartKeys = ['income', 'expense', 'profit']

const dayTotals = computed(() => {
  const totals = {
    income: 0,
    expense: 0,
    profit: 0
  }
  if (props.mode !== 'summary') return totals
  for (const item of props.data) {
    if (!Array.isArray(item.data)) continue
    for (const row of item.data) {
      totals.income += Number(row.amountIncome) || 0
      totals.expense += Number(row.amountExpense) || 0
      totals.profit += Number(row.amountProfit) || 0
    }
  }
  return totals
})

const chartCircles = computed(() => {
  const maxAbs = Number(props.monthChartMaxAbs) || 0
  return chartKeys.map((key) => {
    const dayValue = Number(dayTotals.value[key]) || 0
    const absVal = Math.abs(dayValue)
    const ratio = maxAbs > 0 ? Math.min(1, absVal / maxAbs) : 0
    const proportional = maxAbs > 0 ? Math.round(ratio * CHART_CIRCLE_MAX_PX) : 0
    const size =
      absVal > 0 && maxAbs > 0
        ? Math.min(CHART_CIRCLE_MAX_PX, Math.max(CHART_CIRCLE_MIN_PX, proportional))
        : 0
    return { key, ratio, size, absVal }
  }).filter(c => c.absVal > 0 && c.size > 0)
})

onMounted(() => {
  handleOnTransactionDrop()
})

function handleOnTransactionDrop () {
  if (dayRef.value) {
    dayRef.value.addEventListener('dragenter', (e) => {
      e.target.classList.add('dragover')
    })
    dayRef.value.addEventListener('dragleave', (e) => {
      e.target.classList.remove('dragover')
    })
    dayRef.value.addEventListener('dragover', (e) => {
      e.preventDefault()
    })
    dayRef.value.addEventListener('drop', (e) => {
      e.preventDefault()
      e.target.classList.remove('dragover')

      const targetTransaction = JSON.parse(e.dataTransfer.getData('transaction'))
      if (targetTransaction && (moment(props.date).format('YYYY-MM-DD') !== targetTransaction.date)) {
        store
          .dispatch('transaction/update', {
            ...targetTransaction,
            date: moment(props.date).format('YYYY-MM-DD'),
            apply_to_all_in_future: false
          })
      }
    })
  }
}

function getMonthShort (date) {
  try {
    return date.toLocaleString(locale.replace('_', '-'), { month: 'short' })
  } catch (_e) { }
}

function onClick (e) {
  const onPlus = e.target.closest('.icg-plus')
  if (onPlus) {
    emitter.emit('openAddTransactionModal', {
      defaultDate: dayjs(props.date).format('YYYY-MM-DD')
    })
  } else {
    router.push(`/date/${moment(props.date).format('YYYY-MM-DD')}/`)
  }
}

</script>

<template>
  <div
    ref="dayRef"
    class="absolute align-right custom-p-8 icg-day-slot"
    @click="onClick"
  >
    <button class="circle light-gray icg-plus">
      <i class="fas fa-plus" />
    </button>
    <div class="icg-day">
      <span class="icon size-16">
        <i class="fas fa-exclamation-triangle text-red" />
      </span>
      {{ date.getDate() }} <span v-if="date.getDate() === 1">{{ getMonthShort(date) }}</span>
    </div>
    <div :class="props.mode === 'summary' ? 'icg-day-slot-content-circles' : 'icg-day-slot-content-col'">
      <div />
      <div
        v-if="props.mode === 'summary' && props.isCurrentMonth && chartCircles.length"
        class="icg-charts"
      >
        <span
          v-for="circle in chartCircles"
          :key="circle.key"
          class="icg-chart-circle"
          :class="`icg-chart-circle--${circle.key}`"
          :style="{ width: `${circle.size}px`, height: `${circle.size}px` }"
        />
      </div>
      <template v-if="props.mode === 'operations'">
        <InfiniteCalendarGridDaySlotItem
          v-for="transaction in props.data"
          :key="transaction.id"
          :transaction="transaction"
        />
      </template>
      <template v-else-if="props.mode === 'summary'">
        <InfiniteCalendarGridDaySlotItemSummary
          v-for="summary in props.data"
          :key="summary.date"
          :summary="summary"
        />
      </template>
    </div>
  </div>
</template>

<style lang="scss">

.icg-day-slot .icg-day .icon {
  display: none;
}

.icg-day-slot:has(.icg-row--overdue) .icg-day .icon {
  display: inline-block;
}

.icg-day-slot {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  cursor: pointer;
}

.icg-day-slot-content-circles {
  flex: 1;
  display: grid;
  grid-template-rows: 1fr auto 1fr;
  align-items: end;
}

.icg-day-slot-content-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: end;
}

.dragover {
  outline: 1px solid var(--border-color-hard);
  outline-offset: -1px;
}

.icg-months-grid-day--active-month .icg-day {
  color: var(--text-color-strong);
}

.icg-months-grid-day--current {
  background-color: var(--highlighted-yellow) !important;
}

// .icg-months-grid-day--current .icg-day {
//   background-color: var(--red);
//   color: var(--white);
//   display: flex;
//   justify-content: center;
//   align-items: center;
//   width: 2rem;
//   height: 2rem;
//   border-radius: 100%;
//   margin-left: auto;
//   transform: translateX(.2rem) translateY(-.2rem);

//   span {
//     display: none;
//   }

// }

@media screen and (max-width: 760px) {
  .icg-months-grid-day--current .icg-day {
    width: 1rem;
    height: 1rem;
  }
}

.icg-plus {
  display: none;
  position: absolute;
  top: 0.4rem;
  left: 0.4rem;

  :hover>& {
    display: block;
  }
}

.icg-charts {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 32px;
  margin-bottom: 6px;
}

.icg-chart-circle {
  border-radius: 50%;
  display: inline-block;
}

.icg-chart-circle--income {
  background: var(--green);
}

.icg-chart-circle--expense {
  background: var(--red);
}

.icg-chart-circle--profit {
  background: var(--blue);
}
</style>

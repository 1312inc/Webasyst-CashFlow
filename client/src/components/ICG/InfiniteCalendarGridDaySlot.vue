<script setup>
import { onMounted, ref } from 'vue'
import { locale } from '@/plugins/locale'
import { emitter } from '@/plugins/eventBus'
import InfiniteCalendarGridDaySlotItem from './InfiniteCalendarGridDaySlotItem.vue'
import dayjs from 'dayjs'
import { useRouter } from 'vue-router/composables'
import { moment } from '@/plugins/numeralMoment.js'
import store from '@/store'

const props = defineProps(['date', 'data'])
const router = useRouter()
const dayRef = ref()

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

function onClick () {
  const t = dayRef.value?.querySelector('.icg-plus')
  if (t) {
    if (window.getComputedStyle(t, null).getPropertyValue('display') === 'none') {
      router.push(`/date/${moment(props.date).format('YYYY-MM-DD')}/`)
    } else {
      emitter.emit('openAddTransactionModal', {
        defaultDate: dayjs(props.date).format('YYYY-MM-DD')
      })
    }
  }
}

</script>

<template>
  <div
    ref="dayRef"
    class="absolute align-right custom-p-8"
    style="width: 100%; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer;"
    @click="onClick"
  >
    <button class="circle small light-gray desktop-only icg-plus">
      <i class="fas fa-plus" />
    </button>
    <div class="icg-day">
      {{ date.getDate() }} <span v-if="date.getDate() === 1">{{ getMonthShort(date) }}</span>
    </div>
    <div class="small">
      <InfiniteCalendarGridDaySlotItem
        v-for="transaction in props.data"
        :key="transaction.id"
        :transaction="transaction"
      />
    </div>
  </div>
</template>

<style scoped lang="scss">

.dragover {
  outline: 1px solid var(--border-color-hard);
  outline-offset: -1px;
}

.icg-months-grid-day--active-month .icg-day {
  color: var(--text-color-strong);
}

.icg-months-grid-day--current .icg-day {
  background-color: var(--red);
  color: var(--white);
  display: flex;
  justify-content: center;
  align-items: center;
  width: 2rem;
  height: 2rem;
  border-radius: 100%;
  margin-left: auto;
  transform: translateX(.2rem) translateY(-.2rem);

  span {
    display: none;
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
</style>

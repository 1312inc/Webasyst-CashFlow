<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import flatpickr from 'flatpickr'
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect'
import api from '@/plugins/api'
import store from '@/store'

const incomeCategories = computed(() => store.getters['category/getByType']('income'))
const expenseCategories = computed(() => store.getters['category/getByType']('expense'))
const planData = ref([])
const breakdownData = ref([])
const selectedCurrency = ref('')
const currentMonthFirstDay = ref(new Date().toISOString().slice(0, 7) + '-01')
const monthPickerEl = ref(null)
let monthPicker = null
const isTotalPlanMode = computed(() => currentMonthFirstDay.value == null)
const currencies = computed(() => {
  return breakdownData.value.map(item => item.currency).filter(Boolean)
})
const filteredPlanData = computed(() => {
  if (!selectedCurrency.value) return planData.value
  return planData.value.filter(plan => plan.currency === selectedCurrency.value)
})
const planByCategoryId = computed(() => {
  const result = {}
  for (const plan of filteredPlanData.value) {
    result[plan.category_id] = plan
  }
  return result
})
const selectedBreakdown = computed(() => {
  return breakdownData.value.find(item => item.currency === selectedCurrency.value) || null
})
const factByCategoryId = computed(() => {
  const result = {}
  const breakdown = selectedBreakdown.value
  if (!breakdown) return result
  const groups = [
    { items: breakdown.income?.data || [], sign: 1 },
    { items: breakdown.expense?.data || [], sign: -1 },
    { items: breakdown.profit?.data || [], sign: 1 }
  ]
  for (const group of groups) {
    for (const item of group.items) {
      if (!item?.category_id) continue
      result[item.category_id] = (Number(item.amount) || 0) * group.sign
    }
  }
  return result
})
const allCategories = computed(() => [...incomeCategories.value, ...expenseCategories.value])
const balancePlanTotal = computed(() => {
  return allCategories.value.reduce((sum, category) => {
    const value = Number(getPlanAmount(category.id))
    return Number.isNaN(value) ? sum : sum + value
  }, 0)
})
const balanceFactTotal = computed(() => {
  return allCategories.value.reduce((sum, category) => {
    const value = Number(getFactAmount(category.id))
    return Number.isNaN(value) ? sum : sum + value
  }, 0)
})
const balanceDeviationAmount = computed(() => balanceFactTotal.value - balancePlanTotal.value)
const balanceDeviationPercent = computed(() => {
  if (!balancePlanTotal.value) return ''
  return `${((balanceDeviationAmount.value / balancePlanTotal.value) * 100).toFixed(2)}%`
})

onMounted(() => {
  initMonthPicker()
  fetchData()
})

onBeforeUnmount(() => {
  if (monthPicker) monthPicker.destroy()
})

function initMonthPicker () {
  if (!monthPickerEl.value) return
  monthPicker = flatpickr(monthPickerEl.value, {
    defaultDate: currentMonthFirstDay.value,
    dateFormat: 'Y-m-01',
    altInput: true,
    altFormat: 'F Y',
    plugins: [
      monthSelectPlugin({
        shorthand: true,
        dateFormat: 'Y-m-01',
        altFormat: 'F Y'
      })
    ],
    onChange: (selectedDates) => {
      const selectedDate = selectedDates[0]
      if (!selectedDate) return
      const year = selectedDate.getFullYear()
      const month = String(selectedDate.getMonth() + 1).padStart(2, '0')
      currentMonthFirstDay.value = `${year}-${month}-01`
      fetchData()
    }
  })
}

function setTotalPlanMode () {
  currentMonthFirstDay.value = null
  if (monthPicker) monthPicker.clear()
  fetchData()
}

function getMonthRange () {
  const source = currentMonthFirstDay.value || (new Date().toISOString().slice(0, 7) + '-01')
  const [year, month] = source.split('-').map(Number)
  const from = `${year}-${String(month).padStart(2, '0')}-01`
  const lastDay = new Date(year, month, 0).getDate()
  const to = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
  return { from, to }
}

async function fetchData () {
  try {
    if (!currentMonthFirstDay.value) {
      const nextPlanData = await requestPlanData()
      planData.value = nextPlanData
      return
    }

    const [nextPlanData, nextBreakdownData] = await Promise.all([requestPlanData(), requestBreakdownData()])
    planData.value = nextPlanData
    breakdownData.value = nextBreakdownData

    const nextCurrencies = nextBreakdownData.map(item => item.currency).filter(Boolean)
    if (!nextCurrencies.includes(selectedCurrency.value)) {
      selectedCurrency.value = nextCurrencies[0] || ''
    }
  } catch (_) {}
}

async function requestPlanData () {
  const { data } = await api.get('cash.plan.get', {
    params: {
      date: currentMonthFirstDay.value
    }
  })
  return Array.isArray(data) ? data : []
}

async function requestBreakdownData () {
  const { from, to } = getMonthRange()
  const { data } = await api.get('cash.aggregate.getBreakDown', {
    params: {
      from,
      to,
      filter: 'all',
      children_help_parents: 1
    }
  })
  return Array.isArray(data) ? data : []
}

function getPlanAmount (categoryId) {
  return planByCategoryId.value[categoryId]?.amount ?? ''
}

function getFactAmount (categoryId) {
  if (isTotalPlanMode.value) return ''
  return factByCategoryId.value[categoryId] ?? ''
}

function getDeviationAmount (categoryId) {
  const planAmount = Number(getPlanAmount(categoryId))
  const factAmount = Number(getFactAmount(categoryId))
  if (Number.isNaN(planAmount) || Number.isNaN(factAmount) || planAmount === 0 || isTotalPlanMode.value) return ''
  return factAmount - planAmount
}

function getDeviationPercent (categoryId) {
  const planAmount = Number(getPlanAmount(categoryId))
  const deviationAmount = Number(getDeviationAmount(categoryId))
  if (!planAmount || Number.isNaN(deviationAmount) || planAmount === 0 || isTotalPlanMode.value) return ''
  return `${((deviationAmount / planAmount) * 100).toFixed(2)}%`
}

function getDeviationClass (categoryId) {
  const deviationAmount = Number(getDeviationAmount(categoryId))
  if (Number.isNaN(deviationAmount) || deviationAmount === 0) return ''
  return deviationAmount < 0 ? 'is-negative' : 'is-positive'
}

function getBalanceDeviationClass () {
  if (balanceDeviationAmount.value === 0) return ''
  return balanceDeviationAmount.value < 0 ? 'is-negative' : 'is-positive'
}

function getPlanEntry (categoryId) {
  return planByCategoryId.value[categoryId] || null
}

async function updatePlanAmount (categoryId, amount) {
  const existingPlan = getPlanEntry(categoryId)
  const currency = selectedCurrency.value || existingPlan?.currency || currencies.value[0] || ''
  const payload = {
    id: existingPlan?.id || null,
    amount,
    category_id: categoryId,
    currency,
    date: currentMonthFirstDay.value
  }

  try {
    const { data } = await api.post('cash.plan.set', payload)
    const savedPlan = data && typeof data === 'object'
      ? data
      : { ...payload, amount_fact: existingPlan?.amount_fact ?? 0 }
    const index = planData.value.findIndex(plan =>
      plan.category_id === categoryId && plan.currency === currency
    )
    if (index > -1) {
      planData.value.splice(index, 1, savedPlan)
    } else {
      planData.value.push(savedPlan)
    }
  } catch (_) {}
}
</script>

<template>
  <div>
    <h1>Plan B</h1>
    <div class="flexbox vertical-mobile space-8">
      <div class="month-picker">
        <input
          ref="monthPickerEl"
          type="text"
        >
        <button
          class="total-plan-button"
          :class="{ active: isTotalPlanMode }"
          type="button"
          @click="setTotalPlanMode"
        >
          Общий план
        </button>
      </div>
      <div
        v-if="currencies.length"
        class="toggle"
      >
        <span
          v-for="currency in currencies"
          :key="currency"
          :class="{ selected: selectedCurrency === currency }"
          @click="selectedCurrency = currency"
        >
          {{ currency }}
        </span>
      </div>
    </div>

    <h2>Income Categories</h2>
    <table>
      <thead>
        <tr>
          <th />
          <th class="amount-cell">
            План, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Факт, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, %
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="category in incomeCategories"
          :key="category.id"
        >
          <td
            class="category-name-cell"
            :class="{ 'is-child-category': category.parent_category_id }"
          >
            <div class="flexbox middle">
              <i
                class="icon rounded"
                :style="{ backgroundColor: category.color }"
              />
              <router-link
                class="category-name-link"
                :to="{ name: 'Category', params: { id: category.id } }"
              >
                {{ category.name }}
              </router-link>
            </div>
          </td>
          <td class="amount-cell">
            <input
              class="amount-input"
              type="number"
              :value="getPlanAmount(category.id)"
              @change="updatePlanAmount(category.id, $event.target.value)"
            >
          </td>
          <td class="amount-cell">
            {{ getFactAmount(category.id) || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getDeviationClass(category.id)"
          >
            {{ getDeviationAmount(category.id) || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getDeviationClass(category.id)"
          >
            {{ getDeviationPercent(category.id) || '-' }}
          </td>
        </tr>
      </tbody>
    </table>

    <h2>Expense Categories</h2>
    <table>
      <thead>
        <tr>
          <th />
          <th class="amount-cell">
            План, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Факт, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, %
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="category in expenseCategories"
          :key="category.id"
        >
          <td
            class="category-name-cell"
            :class="{ 'is-child-category': category.parent_category_id }"
          >
            <div class="flexbox middle">
              <i
                class="icon rounded"
                :style="{ backgroundColor: category.color }"
              />
              <router-link
                class="category-name-link"
                :to="{ name: 'Category', params: { id: category.id } }"
              >
                {{ category.name }}
              </router-link>
            </div>
          </td>
          <td class="amount-cell">
            <input
              class="amount-input"
              type="number"
              :value="getPlanAmount(category.id)"
              @change="updatePlanAmount(category.id, $event.target.value)"
            >
          </td>
          <td class="amount-cell">
            {{ getFactAmount(category.id) || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getDeviationClass(category.id)"
          >
            {{ getDeviationAmount(category.id) || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getDeviationClass(category.id)"
          >
            {{ getDeviationPercent(category.id) || '-' }}
          </td>
        </tr>
      </tbody>
    </table>

    <h2>Balance</h2>
    <table>
      <thead>
        <tr>
          <th />
          <th class="amount-cell">
            План, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Факт, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, {{ selectedCurrency }}
          </th>
          <th class="amount-cell">
            Отклонение, %
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="category-name-cell">
            Сальдо
          </td>
          <td class="amount-cell">
            {{ balancePlanTotal || '–' }}
          </td>
          <td class="amount-cell">
            {{ balanceFactTotal || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getBalanceDeviationClass()"
          >
            {{ balanceDeviationAmount || '–' }}
          </td>
          <td
            class="amount-cell"
            :class="getBalanceDeviationClass()"
          >
            {{ balanceDeviationPercent || '–' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style>
@import 'flatpickr/dist/plugins/monthSelect/style.css';

.flatpickr-monthSelect-month {
  padding: 0 6px !important;
}

.month-picker {
  display: flex;
  align-items: center;
  gap: 8px;
}

.total-plan-button.active {
  font-weight: 600;
}

.icon.rounded {
  margin-right: 6px;
}

.is-child-category .icon.rounded {
  margin-left: 12px;
}

.category-name-cell {
  width: 200px;
  max-width: 200px;
}

.category-name-link {
  display: inline-block;
  max-width: calc(200px - 26px);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: middle;
}

.amount-cell {
  width: 140px;
  max-width: 140px;
  min-width: 140px;
}

.amount-input {
  width: 100%;
}

.is-negative {
  color: #c0392b;
}

.is-positive {
  color: #1f9d55;
}

</style>

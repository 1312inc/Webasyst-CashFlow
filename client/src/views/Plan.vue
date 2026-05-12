<!-- eslint-disable vue/multi-word-component-names -->
<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import flatpickr from 'flatpickr'
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect'
import api from '@/plugins/api'
import store from '@/store'
import Modal from '@/components/Modal'
import { appState } from '@/utils/appState'
import { useRoute, useRouter } from 'vue-router/composables'

const route = useRoute()
const router = useRouter()

const TOTAL_PLAN_QUERY_VALUE = 'total'
const MONTH_QUERY_RE = /^\d{4}-\d{2}$/

function getCurrentMonthFirstDay () {
  return new Date().toISOString().slice(0, 7) + '-01'
}

function parseMonthFromQuery (rawValue) {
  if (typeof rawValue !== 'string' || !rawValue) return getCurrentMonthFirstDay()
  if (rawValue === TOTAL_PLAN_QUERY_VALUE) return null
  if (MONTH_QUERY_RE.test(rawValue)) return `${rawValue}-01`
  return getCurrentMonthFirstDay()
}

function parseCurrencyFromQuery (rawValue) {
  return typeof rawValue === 'string' ? rawValue : ''
}

const incomeCategories = computed(() => store.getters['category/getByType']('income'))
const expenseCategories = computed(() => store.getters['category/getByType']('expense'))
const planData = ref([])
const breakdownData = ref([])
const selectedCurrency = ref(parseCurrencyFromQuery(route.query.currency))
const currentMonthFirstDay = ref(parseMonthFromQuery(route.query.month))
const monthPickerEl = ref(null)
const isFetching = ref(false)
const openPremiumModal = ref(false)
let monthPicker = null
let isSyncingUrl = false

const isTotalPlanMode = computed(() => currentMonthFirstDay.value == null)

function collectCurrenciesFromPlan (plans) {
  const seen = new Set()
  const list = []
  for (const plan of plans) {
    const code = plan?.currency
    if (!code || seen.has(code)) continue
    seen.add(code)
    list.push(code)
  }
  return list
}

const currencies = computed(() => {
  if (isTotalPlanMode.value) {
    return collectCurrenciesFromPlan(planData.value)
  }
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

const childrenByParentId = computed(() => {
  const result = {}
  for (const category of allCategories.value) {
    const parentId = category.parent_category_id
    if (!parentId) continue
    if (!result[parentId]) result[parentId] = []
    result[parentId].push(category.id)
  }
  return result
})
function isPlanMissingMonthRange (plan) {
  if (!plan) return false
  const from = plan.from
  const to = plan.to
  return (from == null || from === '') && (to == null || to === '')
}

const ghostAmounts = computed(() => {
  const result = new Set()
  for (const category of allCategories.value) {
    getComputedPlanAmount(category.id, result)
  }
  if (!isTotalPlanMode.value) {
    const visited = new Set()
    for (const plan of [...filteredPlanData.value].sort((a, b) => b.from - a.from).reverse()) {
      if (!plan?.category_id) continue
      if (visited.has(plan.category_id)) continue
      visited.add(plan.category_id)
      if (isPlanMissingMonthRange(plan)) {
        result.add(plan.category_id)
      }
    }
  }
  return result
})

onMounted(() => {
  initMonthPicker()
  fetchData()
})

onBeforeUnmount(() => {
  if (monthPicker) monthPicker.destroy()
})

function buildQueryFromState () {
  const next = { ...route.query }

  if (currentMonthFirstDay.value === null) {
    next.month = TOTAL_PLAN_QUERY_VALUE
  } else {
    const ym = currentMonthFirstDay.value.slice(0, 7)
    if (ym === getCurrentMonthFirstDay().slice(0, 7)) {
      delete next.month
    } else {
      next.month = ym
    }
  }

  if (selectedCurrency.value) {
    next.currency = selectedCurrency.value
  } else {
    delete next.currency
  }

  return next
}

function syncUrl () {
  const next = buildQueryFromState()
  const current = route.query
  const sameKeys = Object.keys(next).length === Object.keys(current).length
  const sameValues = sameKeys && Object.keys(next).every(k => next[k] === current[k])
  if (sameValues) return
  isSyncingUrl = true
  router.replace({ query: next }).catch(() => {}).finally(() => {
    isSyncingUrl = false
  })
}

watch(currentMonthFirstDay, syncUrl)
watch(selectedCurrency, syncUrl)

watch(() => route.query, (next, prev) => {
  if (isSyncingUrl) return
  const nextMonth = parseMonthFromQuery(next.month)
  const nextCurrency = parseCurrencyFromQuery(next.currency)
  let needFetch = false
  if (nextMonth !== currentMonthFirstDay.value) {
    currentMonthFirstDay.value = nextMonth
    if (monthPicker) {
      if (nextMonth) {
        monthPicker.setDate(nextMonth, false)
      } else {
        monthPicker.clear()
      }
    }
    needFetch = true
  }
  if (nextCurrency !== selectedCurrency.value && (nextCurrency || prev.currency)) {
    selectedCurrency.value = nextCurrency
  }
  if (needFetch) fetchData()
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

let fetchToken = 0
async function fetchData () {
  const token = ++fetchToken
  isFetching.value = true
  try {
    if (!currentMonthFirstDay.value) {
      const nextPlanData = await requestPlanData()
      if (token !== fetchToken) return
      planData.value = nextPlanData

      const nextCurrencies = collectCurrenciesFromPlan(nextPlanData)
      if (!nextCurrencies.includes(selectedCurrency.value)) {
        selectedCurrency.value = nextCurrencies[0] || ''
      }
      return
    }

    const [nextPlanData, nextBreakdownData] = await Promise.all([requestPlanData(), requestBreakdownData()])
    if (token !== fetchToken) return
    planData.value = nextPlanData
    breakdownData.value = nextBreakdownData

    const nextCurrencies = nextBreakdownData.map(item => item.currency).filter(Boolean)
    if (!nextCurrencies.includes(selectedCurrency.value)) {
      selectedCurrency.value = nextCurrencies[0] || ''
    }
  } catch (_) {
  } finally {
    if (token === fetchToken) isFetching.value = false
  }
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

function getOwnPlanAmount (categoryId) {
  const amount = planByCategoryId.value[categoryId]?.amount
  if (amount === null || typeof amount === 'undefined' || amount === '') return null
  return Number(amount)
}

function getComputedPlanAmount (categoryId, ghostSet, visited = new Set()) {
  if (visited.has(categoryId)) return null
  visited.add(categoryId)

  const ownAmount = getOwnPlanAmount(categoryId)
  if (ownAmount !== null) return ownAmount

  const children = childrenByParentId.value[categoryId] || []
  if (!children.length) return null

  let sum = 0
  let hasChildAmount = false
  for (const childId of children) {
    const childAmount = getComputedPlanAmount(childId, ghostSet, new Set(visited))
    if (childAmount === null) continue
    hasChildAmount = true
    sum += childAmount
  }

  if (!hasChildAmount) return null
  if (ghostSet) ghostSet.add(categoryId)
  return sum
}

function getPlanAmount (categoryId) {
  const amount = getComputedPlanAmount(categoryId, null)
  return amount === null ? '' : amount
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
    date: isTotalPlanMode.value ? null : currentMonthFirstDay.value
  }

  try {
    const { data } = await api.post('cash.plan.set', payload)
    if (data && typeof data === 'object') {
      const index = planData.value.findIndex(plan =>
        plan.id === data.id
      )
      if (index > -1) {
        planData.value.splice(index, 1, data)
      } else {
        planData.value.push(data)
      }
    } else {
      const index = planData.value.findIndex(plan =>
        plan.id === payload.id
      )
      if (index > -1) {
        planData.value.splice(index, 1)
      }
    }
  } catch (e) {
    if (e.response?.status === 402) {
      openPremiumModal.value = true
    }
  }
}

function onClickGoToPremium () {
  openPremiumModal.value = false
  window.location.href = `${appState.baseUrl}upgrade/`
}
</script>

<template>
  <div class="box custom-p-16">
    <h1>{{ $t('planView.title') }}</h1>
    <div class="flexbox vertical-mobile space-8">
      <div class="flexbox middle space-8">
        <div class="month-picker">
          <input
            ref="monthPickerEl"
            type="text"
            class="button light-gray"
          >
        </div>
        <div style="flex: 1; overflow: hidden;">
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
      </div>
      <button
        class="total-plan-button nowrap nobutton"
        :class="{ active: isTotalPlanMode }"
        type="button"
        @click="setTotalPlanMode"
      >
        {{ $t('planView.totalPlanButton') }}
      </button>
    </div>

    <div
      v-if="!appState.isPremium"
      class="alert info"
    >
      {{ $t('planView.premiumAlert') }}
    </div>

    <h4 class="gray custom-mb-4">
      {{ $t('planView.incomeCategoriesTitle') }}
    </h4>

    <div class="plan-table-scroll custom-mt-4">
      <table class="bigdata">
        <thead>
          <tr>
            <th />
            <th class="amount-cell">
              {{ $t('planView.columnPlanWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnFactWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationPercent') }}
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
                <span
                  v-if="category.glyph"
                  :key="category.color"
                  class="icon"
                >
                  <i
                    :class="category.glyph"
                    :style="`color:${category.color};`"
                  />
                </span>
                <span
                  v-else
                  class="icon"
                >
                  <i
                    class="rounded"
                    :style="`background-color:${category.color};`"
                  />
                </span>
                <router-link
                  class="category-name-link"
                  :to="{ name: 'Category', params: { id: category.id } }"
                >
                  {{ category.name }}
                </router-link>
              </div>
            </td>
            <td
              class="amount-cell"
              :class="{ 'is-ghost-amount': ghostAmounts.has(category.id) }"
            >
              <input
                class="amount-input bold"
                type="number"
                :value="ghostAmounts.has(category.id) ? '' : getPlanAmount(category.id)"
                :placeholder="ghostAmounts.has(category.id) ? getPlanAmount(category.id) : ''"
                :disabled="isFetching"
                @change="updatePlanAmount(category.id, $event.target.value)"
              >
            </td>
            <td class="amount-cell">
              {{ getFactAmount(category.id) || '—' }}
            </td>
            <td
              class="amount-cell bold"
              :class="[getDeviationClass(category.id), { 'is-ghost-amount': ghostAmounts.has(category.id) }]"
            >
              {{ getDeviationAmount(category.id) || '—' }}
            </td>
            <td
              class="amount-cell"
              :class="[getDeviationClass(category.id), { 'is-ghost-amount': ghostAmounts.has(category.id) }]"
            >
              {{ getDeviationPercent(category.id) || '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <h4 class="gray custom-mb-4">
      {{ $t('planView.expenseCategoriesTitle') }}
    </h4>
    <div class="plan-table-scroll custom-mt-4">
      <table class="bigdata">
        <thead>
          <tr>
            <th />
            <th class="amount-cell">
              {{ $t('planView.columnPlanWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnFactWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationPercent') }}
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
                <span
                  v-if="category.glyph"
                  :key="category.color"
                  class="icon"
                >
                  <i
                    :class="category.glyph"
                    :style="`color:${category.color};`"
                  />
                </span>
                <span
                  v-else
                  class="icon"
                >
                  <i
                    class="rounded"
                    :style="`background-color:${category.color};`"
                  />
                </span>
                <router-link
                  class="category-name-link"
                  :to="{ name: 'Category', params: { id: category.id } }"
                >
                  {{ category.name }}
                </router-link>
              </div>
            </td>
            <td
              class="amount-cell"
              :class="{ 'is-ghost-amount': ghostAmounts.has(category.id) }"
            >
              <input
                class="amount-input bold"
                type="number"
                :value="ghostAmounts.has(category.id) ? '' : getPlanAmount(category.id)"
                :placeholder="ghostAmounts.has(category.id) ? getPlanAmount(category.id) : ''"
                :disabled="isFetching"
                @change="updatePlanAmount(category.id, $event.target.value)"
              >
            </td>
            <td class="amount-cell">
              {{ getFactAmount(category.id) || '—' }}
            </td>
            <td
              class="amount-cell bold"
              :class="[getDeviationClass(category.id), { 'is-ghost-amount': ghostAmounts.has(category.id) }]"
            >
              {{ getDeviationAmount(category.id) || '—' }}
            </td>
            <td
              class="amount-cell"
              :class="[getDeviationClass(category.id), { 'is-ghost-amount': ghostAmounts.has(category.id) }]"
            >
              {{ getDeviationPercent(category.id) || '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <h4 class="gray custom-mb-4">
      {{ $t('planView.balanceSectionTitle') }}
    </h4>
    <div class="plan-table-scroll custom-mt-4">
      <table class="bigdata">
        <thead>
          <tr>
            <th />
            <th class="amount-cell">
              {{ $t('planView.columnPlanWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnFactWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationWithCurrency', { currency: selectedCurrency }) }}
            </th>
            <th class="amount-cell">
              {{ $t('planView.columnDeviationPercent') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="category-name-cell">
              {{ $t('planView.balanceRowLabel') }}
            </td>
            <td class="amount-cell bold">
              {{ balancePlanTotal || '—' }}
            </td>
            <td class="amount-cell">
              {{ balanceFactTotal || '—' }}
            </td>
            <td
              class="amount-cell bold"
              :class="getBalanceDeviationClass()"
            >
              {{ balanceDeviationAmount || '—' }}
            </td>
            <td
              class="amount-cell"
              :class="getBalanceDeviationClass()"
            >
              {{ balanceDeviationPercent || '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <portal v-if="openPremiumModal">
      <Modal @close="openPremiumModal = false">
        <div class="dialog-body">
          <div class="dialog-content">
            {{ $t('planView.premiumDialogText') }}
          </div>
          <div class="dialog-footer">
            <button
              class="button"
              @click="onClickGoToPremium"
            >
              {{ $t('planView.premiumDialogPrimaryButton') }}
            </button>
            <button
              class="button outlined light-gray"
              @click="openPremiumModal = false"
            >
              {{ $t('close') }}
            </button>
          </div>
        </div>
      </Modal>
    </portal>
  </div>
</template>

<style>
@import 'flatpickr/dist/plugins/monthSelect/style.css';

.plan-table-scroll {
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-x: contain;
}

.plan-table-scroll table {
  width: 100%;
  min-width: 760px;
}

@media screen and (max-width: 760px) {
  .plan-table-scroll {
    margin-left: -16px;
    margin-right: -16px;
    padding-left: 16px;
    padding-right: 16px;
  }
}

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

.icon {
  margin-right: 6px;
}

.is-child-category .icon {
  margin-left: 12px;
}

.category-name-cell {
  width: 200px;
  max-width: 200px;
}

@media screen and (max-width: 760px) {
  .category-name-cell {
    width: 90px;
    max-width: 90px;
  }
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
  text-align: right;
}

@media screen and (max-width: 760px) {
  .amount-cell {
    width: 70px;
    max-width: 70px;
    min-width: 70px;
  }
}

.amount-input {
  width: 100%;
  border-width: 0px !important;
}

.is-ghost-amount .amount-input {
  color: var(--light-gray) !important;
}
.is-ghost-amount .amount-input::placeholder {
  color: var(--light-gray) !important;
}

.amount-input:hover {
  opacity: .5;
}

.is-negative {
  color: #c0392b;
}
.is-negative.is-ghost-amount {
  opacity: 0.5;
}

.is-positive {
  color: #1f9d55;
}

.is-positive.is-ghost-amount {
  opacity: 0.5;
}

</style>

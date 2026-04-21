<script setup>
import BlankBox from '../components/BlankBox.vue'
import AmChartTarget from './Charts/AmChartTarget.vue'
import { useStorage } from '@vueuse/core'
import { computed, ref, shallowRef, watch } from 'vue'
import api from '@/plugins/api'
import moment from 'moment'
import { useRoute } from 'vue-router/composables'
import store from '@/store'
import { helpers } from '@/plugins/helpers'

const route = useRoute()

const activeCurrencyParams = computed(() => {
  if (route.name === 'Account') return { account_id: route.params.id }
  if (route.name === 'Currency') return { currency: route.params.id }
  return {}
})

const cashTargetBlockHidden = useStorage('cashTargetBlockHidden', { value: false, expiredAt: '' }, localStorage)

// Сбросить флаг, если прошло 30 дней
if (cashTargetBlockHidden.value.value && cashTargetBlockHidden.value.expiredAt) {
  if (new Date(cashTargetBlockHidden.value.expiredAt) < new Date()) {
    cashTargetBlockHidden.value.value = false
    cashTargetBlockHidden.value.expiredAt = ''
  }
}

const isPromoMode = !window.appState.isPremium // TODO: add check for premium
const isFetching = ref(false)
const isEmptyMode = ref(false)
const chartData = shallowRef(null)
const currentCategoryId = ref(null)
const currentMonthLabel = ref(moment().format('MMMM YYYY'))

const categories = computed(() => {
  const data = chartData.value
  if (!data || !data.length) return []
  const seen = new Set()
  const result = []
  for (const i of data) {
    if (!i.category_id || seen.has(i.category_id)) continue
    seen.add(i.category_id)
    const category = store.getters['category/getById'](i.category_id)
    if (!category) continue
    result.push({
      id: i.category_id,
      name: category.name,
      color: category.color,
      amount: i.amount,
      amountFact: i.amount_fact,
      currency: i.currency
    })
  }
  return result
})

const currentCategory = computed(() => {
  if (currentCategoryId.value == null) return null
  return categories.value.find(i => i.id === currentCategoryId.value) || null
})

const chartState = computed(() => ({
  isPromoMode,
  isEmptyMode: isEmptyMode.value,
  amount: currentCategory.value?.amount ?? 50,
  amountFact: currentCategory.value?.amountFact ?? 50,
  currencyCode: currentCategory.value?.currency ?? '',
  color: currentCategory.value?.color ?? ''
}))

let fetchToken = 0

watch(activeCurrencyParams, (value) => {
  fetchTarget(value)
}, { immediate: true })

function fetchTarget (params) {
  if (cashTargetBlockHidden.value.value) return
  if (isPromoMode) return
  const token = ++fetchToken
  isFetching.value = true
  api
    .get('cash.plan.get', {
      params: {
        date: moment().format('YYYY-MM-DD'),
        ...params
      }
    })
    .then(({ data }) => {
      if (token !== fetchToken) return
      chartData.value = data
      if (data && data.length > 0) {
        isEmptyMode.value = false
        currentCategoryId.value = +data[0].category_id
      } else {
        isEmptyMode.value = true
        currentCategoryId.value = null
      }
    })
    .finally(() => {
      if (token === fetchToken) isFetching.value = false
    })
}

function closeTarget () {
  const expiredAt = new Date()
  expiredAt.setDate(expiredAt.getDate() + 30)
  cashTargetBlockHidden.value.value = true
  cashTargetBlockHidden.value.expiredAt = expiredAt.toISOString()
}

function onCategoryChange (id) {
  currentCategoryId.value = +id
}
</script>

<template>
  <div
    v-if="!(cashTargetBlockHidden.value && isPromoMode)"
    class="c-details-container__target"
  >
    <BlankBox
      :disable-bottom-margin="true"
      class="custom-p-24 flexbox middle"
    >
      <div
        v-if="isFetching"
        class="custom-mx-auto"
      >
        <div class="spinner custom-p-16" />
      </div>
      <div
        v-else
        class="custom-mx-auto"
      >
        <a
          v-if="isPromoMode"
          href="#"
          class="c-details-container__target__close"
          @click.prevent="closeTarget"
        >
          <i class="fas fa-times gray" />
        </a>
        <div class="custom-mx-auto">
          <AmChartTarget
            :is-promo-mode="isPromoMode"
            :is-empty-mode="isEmptyMode"
            :amount="chartState.amount"
            :amount-fact="chartState.amountFact"
            :color="chartState.color"
          />

          <template v-if="isPromoMode">
            <h5 class="align-center custom-mt-0 custom-mb-12">
              {{ $t('detailsTargetDescTitle') }}
            </h5>
            <p class="small gray align-center width-90 custom-mx-auto custom-my-12">
              {{ $t('detailsTargetDesc') }}
            </p>
            <div class="align-center custom-my-16">
              <a
                :href="`${$helper.baseUrl}upgrade/`"
                class="button small green"
              >{{ $t('detailsTargetDescLink') }}</a>
            </div>
          </template>
          <template v-else-if="isEmptyMode">
            <h5 class="align-center custom-mt-0 custom-mb-12">
              {{ $t('detailsTargetPlanNotSet') }}
            </h5>
            <div class="align-center custom-my-16">
              <a
                :href="`${$helper.baseUrl}plan/`"
                class="button small light-gray"
              >{{ $t('detailsTargetSetGoal') }}</a>
            </div>
          </template>
          <template v-else-if="currentCategory">
            <h5 class="align-center custom-mt-0 custom-mb-12">
              {{ currentCategory.name }}
            </h5>
            <div class="custom-mb-16">
              <span style="text-transform: capitalize;">{{ currentMonthLabel }}</span>
              <br>{{ $t('detailsTargetPlanLabel') }}: {{
                helpers.toCurrency({
                  value: chartState.amount,
                  currencyCode: chartState.currencyCode
                })
              }}
              <br>{{ $t('detailsTargetFactForecastLabel') }}: {{
                helpers.toCurrency({
                  value: chartState.amountFact,
                  currencyCode: chartState.currencyCode
                })
              }}
            </div>
          </template>

          <div
            v-if="categories.length"
            class="wa-select solid width-100"
          >
            <select @change="(event) => { onCategoryChange(event.target.value) }">
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </BlankBox>
  </div>
</template>

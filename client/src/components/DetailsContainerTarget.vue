<script setup>
import BlankBox from '../components/BlankBox.vue'
import AmChartTarget from './Charts/AmChartTarget.vue'
import { useStorage } from '@vueuse/core'
import { computed, ref, watch } from 'vue'
import api from '@/plugins/api'
import moment from 'moment'
import { useRoute } from 'vue-router/composables'
import store from '@/store'
import { helpers } from '@/plugins/helpers'

const route = useRoute()
const activeCurrencyCode = computed(() => {
  let param = ''
  if (route.name === 'Account') {
    param = 'account_id'
  }
  if (route.name === 'Currency') {
    param = 'currency'
  }
  return {
    [param]: route.params.id
  }
})

const cashTargetBlockHidden = useStorage('cashTargetBlockHidden', { value: false, expiredAt: '' }, localStorage)

const currentCategory = ref(null)

const isFetching = ref(false)
const chartData = ref(null)

const chartState = ref({
  isPromoMode: !window.appState.isPremium, // TODO: add check for premium
  isEmptyMode: false,
  amount: 50,
  amountFact: 50,
  currencyCode: ''
})

const categories = computed(() => {
  if (!chartData.value) return []
  // Deduplicate by category_id
  const uniqueByCategory = []
  const seenCategories = new Set()
  for (const i of chartData.value) {
    if (i.category_id && !seenCategories.has(i.category_id)) {
      seenCategories.add(i.category_id)
      uniqueByCategory.push(i)
    }
  }
  return uniqueByCategory.map(i => ({
    name: store.getters['category/getById'](i.category_id).name,
    id: i.category_id,
    amount: i.amount,
    amountFact: i.amount_fact,
    currency: i.currency
  }))
})

// Сбросить, если 30 дней прошло
if (cashTargetBlockHidden.value.value && cashTargetBlockHidden.value.expiredAt) {
  const expired = new Date(cashTargetBlockHidden.value.expiredAt) < new Date()
  if (expired) {
    cashTargetBlockHidden.value.value = false
    cashTargetBlockHidden.value.expiredAt = ''
  }
}

watch(activeCurrencyCode, (value) => {
  fetchTarget(value)
}, { immediate: true })

function fetchTarget (params) {
  if (cashTargetBlockHidden.value.value) return
  if (chartState.value.isPromoMode) return
  isFetching.value = true
  api
    .get('cash.plan.get', {
      params: {
        date: moment().format('YYYY-MM-DD'),
        ...params
      }
    })
    .then(({ data }) => {
      chartData.value = data
      if (data.length > 0) {
        onCategoryChange(data[0].category_id)
        chartState.value.isEmptyMode = false
      } else {
        chartState.value.isEmptyMode = true
      }
    })
    .finally(() => {
      isFetching.value = false
    })
}

function closeTarget () {
  const expiredAt = new Date()
  expiredAt.setDate(expiredAt.getDate() + 30)

  cashTargetBlockHidden.value.value = true
  cashTargetBlockHidden.value.expiredAt = expiredAt.toISOString()
}

function onCategoryChange (id) {
  currentCategory.value = categories.value.find(i => i.id === +id)
  if (currentCategory.value) {
    chartState.value.amount = currentCategory.value.amount
    chartState.value.amountFact = currentCategory.value.amountFact
    chartState.value.currencyCode = currentCategory.value.currency
  }
}
</script>

<template>
  <div
    v-if="!(cashTargetBlockHidden.value && chartState.isPromoMode)"
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
          v-if="chartState.isPromoMode"
          href="#"
          class="c-details-container__target__close"
          @click.prevent="closeTarget"
        >
          <i class="fas fa-times gray" />
        </a>
        <div class="custom-mx-auto">
          <AmChartTarget
            :is-promo-mode="chartState.isPromoMode"
            :is-empty-mode="chartState.isEmptyMode"
            :amount="chartState.amount"
            :amount-fact="chartState.amountFact"
          />

          <template v-if="chartState.isPromoMode">
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
          <template v-else-if="chartState.isEmptyMode">
            <h5 class="align-center custom-mt-0 custom-mb-12">
              План не задан
            </h5>
            <div class="align-center custom-my-16">
              <a
                :href="`${$helper.baseUrl}plan/`"
                class="button small light-gray"
              >Задать цель</a>
            </div>
          </template>
          <template v-else-if="currentCategory">
            <h5 class="align-center custom-mt-0 custom-mb-12">
              {{ currentCategory.name }}
            </h5>
            <div class="custom-mb-16">
              <span style="text-transform: capitalize;">{{ moment().format('MMMM YYYY') }}</span>
              <br>План: {{
                helpers.toCurrency({
                  value: chartState.amount,
                  currencyCode: chartState.currencyCode
                })
              }}
              <br>Факт + прогноз: {{
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

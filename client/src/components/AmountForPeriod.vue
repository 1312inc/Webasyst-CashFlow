<template>
  <div v-if="showComponent">
    <div
      v-if="!showSkeleton"
      :class="{
        'tw-text-green-500': type === 'income',
        'tw-text-red-500': type === 'expense',
      }"
    >
      <div>
        <i
          class="fas"
          :class="{
            'fa-caret-up': type === 'income',
            'fa-caret-down': type === 'expense',
          }"
        ></i
        >&nbsp;
        <span class="small">{{ $helper.toCurrency(total, currency) }}</span>
      </div>
    </div>
    <div v-else class="skeleton">
      <span class="skeleton-line custom-mb-0"></span>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  props: {
    type: {
      type: String,
      required: true
    },

    period: {
      type: String,
      required: true
    }
  },

  computed: {
    ...mapState('transaction', ['chartData', 'chartDataCurrencyIndex']),

    currentType () {
      return this.$store.getters.getCurrentType?.type
    },

    showComponent () {
      return !this.currentType || (this.chartData && this.type === this.currentType)
    },

    currency () {
      return this.chartData?.[this.chartDataCurrencyIndex].currency
    },

    total () {
      const currencyData = this.chartData?.find(
        d => d.currency === this.currency
      )
      if (!currencyData) return false

      const itoday = this.$moment()
      const eltype = this.type === 'income' ? 'amountIncome' : 'amountExpense'

      const result = currencyData.data
        .filter(e => {
          const diff = this.$moment(e.period).diff(itoday, 'days')
          if (this.period === 'to') {
            return diff > 0
          } else {
            return diff <= 0
          }
        })
        .reduce((acc, e) => {
          return acc + e[eltype]
        }, 0)

      return result
    },

    showSkeleton () {
      return this.$store.state.transaction.loadingChart
    }
  }
}
</script>

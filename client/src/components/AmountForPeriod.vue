<template>
  <div
    v-if="!showSkeleton"
    :class="{
      'text-green': type === 'income',
      'text-red': type === 'expense',
      'text-blue': type === 'profit',
    }"
    :title="type === 'profit' ? $t('profit') : ( type === 'income' ? $t('income'): $t('expense') )"
  >
    <div class="custom-ml-12">
      <i v-if="type === 'profit'" class="fas fa-coins text-blue small"></i>
      <span class="small semibold">{{
        $helper.toCurrency({
          value: total,
          currencyCode: currencyCode,
          prefix: type === "income" ? "+ " : type === "expense" ? "− " : " ",
        })
      }}</span>
    </div>
  </div>
  <div v-else class="skeleton">
    <span class="skeleton-line custom-mb-4"></span>
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

    currentChartData () {
      return this.chartData[this.chartDataCurrencyIndex]
    },

    currencyCode () {
      return this.currentChartData?.currency
    },

    total () {
      if (!this.currentChartData) return 0

      const itoday = this.$helper.currentDate
      const eltype = `amount${this.type[0].toUpperCase()}${this.type.slice(1)}`
      const result = this.currentChartData.data
        .filter(e => {
          if (this.period === 'to') {
            return e.period > itoday
          } else {
            return e.period <= itoday
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

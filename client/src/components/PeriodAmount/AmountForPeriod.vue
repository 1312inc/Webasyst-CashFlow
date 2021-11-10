<template>
  <div>
    <div v-for="(type, i) in $_amountMixin_amountTypes" :key="i">
      <div
        v-if="!showSkeleton"
        :class="{
          'text-green': type === 'income',
          'text-red': type === 'expense',
          'text-blue': type === 'profit'
        }"
        :title="
          type === 'profit'
            ? $t('profit')
            : type === 'income'
            ? $t('income')
            : $t('expense')
        "
      >
        <div class="custom-ml-12">
          <i v-if="type === 'profit'" class="fas fa-coins text-blue small"></i>
          <span class="small semibold">{{
            $helper.toCurrency({
              value: total(type),
              currencyCode: currencyCode,
              prefix: type === "income" ? "+ " : type === "expense" ? "− " : " "
            })
          }}</span>
        </div>
      </div>
      <div v-else class="skeleton">
        <span class="skeleton-line custom-mb-4"></span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import amountMixin from '@/mixins/amountMixin'
export default {
  props: {
    period: {
      type: String,
      required: true
    }
  },

  mixins: [amountMixin],

  computed: {
    ...mapState('transaction', ['chartData', 'chartDataCurrencyIndex', 'loadingChart']),

    currentChartData () {
      return this.chartData[this.chartDataCurrencyIndex]
    },

    currencyCode () {
      return this.currentChartData?.currency
    },

    showSkeleton () {
      return this.loadingChart
    }
  },
  methods: {
    total (type) {
      if (!this.currentChartData) return 0

      const itoday = this.$helper.currentDate
      const eltype = `amount${type[0].toUpperCase()}${type.slice(1)}`
      const result = this.currentChartData.data
        .filter(e =>
          this.period === 'to' ? e.period > itoday : e.period <= itoday
        )
        .reduce((acc, e) => acc + e[eltype], 0)
      return result
    }
  }
}
</script>

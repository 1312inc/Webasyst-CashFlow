<template>
  <div v-if="this.$store.state.transaction.chartData.length > 1" class="toggle">
    <span
      @click="activeCurrencyChart = i"
      v-for="(item, i) in this.$store.state.transaction.chartData"
      :key="item.currency"
      :class="{ selected: i === activeCurrencyChart }"
    >
      {{ item.currency }}
    </span>
  </div>
</template>

<script>
export default {
  computed: {
    activeCurrencyChart: {
      get () {
        return this.$store.state.transaction.chartDataCurrencyIndex
      },
      set (val) {
        this.$store.commit('transaction/setChartDataCurrencyIndex', val)
      }
    }
  }
}
</script>

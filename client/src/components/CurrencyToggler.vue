<template>
  <div
    v-if="$store.state.transaction.chartData.length > 1"
    class="toggle"
  >
    <span
      v-for="(item, i) in $store.state.transaction.chartData"
      :key="item.currency"
      :class="{ selected: i === activeCurrencyChart }"
      @click="activeCurrencyChart = i"
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

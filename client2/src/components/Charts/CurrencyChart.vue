<template>
  <div
    class="c-bwc-container custom-mt-8"
    :style="`width: ${width}px; height: ${height}px`"
  >
    <svg ref="chart"></svg>
  </div>
</template>

<script>
import { CurrencyChartD3 } from '@/utils/currencyChartD3'
export default {
  props: {
    currency: {
      type: Object,
      requred: true
    }
  },

  data () {
    return {
      width: 200,
      height: 40
    }
  },

  watch: {
    currency: function ({ data }) {
      if (this.chart) {
        this.chart.renderChart(data)
      }
    }
  },

  mounted () {
    if (!this.chart) {
      this.chart = new CurrencyChartD3(
        this.$refs.chart,
        this.width,
        this.height,
        this.$moment()
          .add(-1, 'M')
          .toDate(),
        this.$moment()
          .add(3, 'M')
          .toDate()
      ).renderChart(this.currency.data)
    }
  }
}
</script>

<style lang="scss">
.c-bwc-container {
  margin: 0 auto;

  svg {
    max-width: initial !important;
  }
}
</style>

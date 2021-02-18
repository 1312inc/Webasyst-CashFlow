<template>
    <div class="c-breakdown-details-chart smaller" ref="chart"></div>
</template>

<script>
import { locale } from '@/plugins/locale'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {
  props: {
    data: {
      type: Array,
      default () {
        return []
      }
    },

    currency: {
      type: String,
      required: true
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU
    chart.innerRadius = am4core.percent(45)

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category_name'
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    this.chart = chart
    this.$watch(
      'data',
      () => {
        this.renderChart()
      },
      {
        deep: true,
        immediate: true
      }
    )
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    renderChart () {
      if (this.data.length) {
        this.chart.series.getIndex(0).slices.template.tooltipText = `{category}: {value.formatNumber('#,###.##')} ${this.$helper.currencySignByCode(this.currency)}`
        this.chart.data = this.data
      } else {
        this.chart.series.getIndex(0).slices.template.tooltipText = this.$t('emptyList')
        this.chart.data = [{
          amount: 100,
          category: 'empty',
          category_color: '#eee'
        }]
      }
    }
  }
}
</script>

<style>
.c-breakdown-details-chart {
    height: 400px;
}
</style>

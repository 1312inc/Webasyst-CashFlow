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

  watch: {
    data () {
      this.renderChart()
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU
    chart.innerRadius = am4core.percent(40)

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category_name'
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    pieSeries.slices.template.stroke = am4core.color('#fff')
    pieSeries.slices.template.strokeOpacity = 1
    pieSeries.legendSettings.itemValueText = `{value} ${this.$helper.currencySignByCode(this.currency)}`

    // Add Legend
    chart.legend = new am4charts.Legend()
    chart.legend.position = 'right'
    chart.legend.valign = 'top'
    chart.legend.maxWidth = 200
    chart.legend.valueLabels.template.align = 'right'
    chart.legend.valueLabels.template.textAlign = 'end'

    this.chart = chart

    this.renderChart()
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    renderChart () {
      if (this.data.length) {
        this.chart.legend.disabled = false
        this.chart.series.getIndex(0).slices.template.tooltipText = `{category}: {value.formatNumber('#,###.##')} ${this.$helper.currencySignByCode(this.currency)}`
        this.chart.data = this.data
      } else {
        this.chart.legend.disabled = true
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

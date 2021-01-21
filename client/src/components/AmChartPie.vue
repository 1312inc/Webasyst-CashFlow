<template>
    <div class="chart smaller" ref="chart"></div>
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
    pieSeries.slices.template.tooltipText = "{category}: {value.formatNumber('#,###.##')}"
    pieSeries.legendSettings.itemValueText = '{value}'

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
      this.chart.data = this.data
    }
  }
}
</script>

<style>
.chart {
    height: 240px;
}
</style>

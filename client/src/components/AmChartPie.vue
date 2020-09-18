<template>
    <div class="chart" ref="chart"></div>
</template>

<script>
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

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    chart.language.locale = am4langRU

    chart.data = this.data

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category_name'
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    pieSeries.innerRadius = am4core.percent(40)
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.stroke = am4core.color('#4a2abb')

    chart.legend = new am4charts.Legend()
    chart.legend.position = 'right'

    this.chart = chart
  }
}
</script>

<style>
.chart {
    height: 300px;
}
</style>

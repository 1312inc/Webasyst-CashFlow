<template>
    <div class="chart smaller" ref="chart"></div>
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

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category_name'
    pieSeries.innerRadius = am4core.percent(40)
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    pieSeries.slices.template.stroke = am4core.color('#fff')
    pieSeries.slices.template.strokeOpacity = 0.5

    // Add Legend
    chart.legend = new am4charts.Legend()
    chart.legend.position = 'right'
    chart.legend.valign = 'top'
    chart.legend.maxWidth = 160

    this.chart = chart

    this.renderChart()

    this.$watch('data', () => {
      this.renderChart()
    })
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

<template>
  <div
    :style="{ width: `${width / widthСorrection}%` }"
    class="c-breakdown-bar-chart"
    ref="chart"
  ></div>
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
    width: {
      type: Number,
      required: true
    }
  },

  data () {
    return {
      widthСorrection: 2
    }
  },

  created () {
    this.mediaQueryList = window.matchMedia('(max-width: 760px)')
    this.widthСorrection = this.mediaQueryList.matches ? 1 : 2
    this.mediaListener = this.mediaQueryList.addListener(this.screenTest)
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.XYChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU

    chart.padding(0, 0, 0, 0)

    // Create axes
    var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis())
    categoryAxis.dataFields.category = 'category'
    categoryAxis.renderer.grid.template.disabled = true
    categoryAxis.renderer.labels.template.disabled = true
    categoryAxis.cursorTooltipEnabled = false
    categoryAxis.data = [
      {
        category: 'category'
      }
    ]

    var valueAxis = chart.xAxes.push(new am4charts.ValueAxis())
    valueAxis.min = 0
    valueAxis.strictMinMax = true
    valueAxis.renderer.grid.template.disabled = true
    valueAxis.renderer.baseGrid.disabled = true
    valueAxis.renderer.labels.template.disabled = true
    valueAxis.cursorTooltipEnabled = false

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
    this.mediaQueryList.removeListener(this.screenTest)
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    createSeries (element) {
      const series = this.chart.series.push(new am4charts.ColumnSeries())
      series.dataFields.valueX = 'amount'
      series.dataFields.categoryY = 'category'
      series.columns.template.propertyFields.fill = 'category_color'
      series.columns.template.strokeWidth = 0
      series.columns.template.width = am4core.percent(100)
      series.stacked = true
      series.name = element.category_name
      series.data = [element]
    },

    renderChart () {
      this.chart.series.clear()
      this.data.forEach(element => {
        this.createSeries({
   ***REMOVED***element,
          category: 'category'
        })
      })
    },

    screenTest (e) {
      this.widthСorrection = e.matches ? 1 : 2
    }
  }
}
</script>

<style>
.c-breakdown-bar-chart {
  height: 50px;
  min-width: 4px;
}
</style>

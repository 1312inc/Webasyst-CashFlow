<template>
    <div>
        <div id="chartdiv"></div>
    </div>
</template>

<script>

import { mapState } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themesAnimated from '@amcharts/amcharts4/themes/animated'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

am4core.useTheme(am4themesAnimated)

export default {

  watch: {
    listItems () {
      this.renderChart()
    }
  },

  computed: {
    ...mapState('transaction', {
      listItems: state => state.fakeData
    })
  },

  mounted () {
    const chart = am4core.create('chartdiv', am4charts.XYChart)
    chart.language.locale = am4langRU

    // Create axes
    const dateAxis = chart.xAxes.push(new am4charts.DateAxis())
    dateAxis.groupData = true
    dateAxis.groupCount = 180
    dateAxis.groupIntervals.setAll([
      { timeUnit: 'day', count: 1 },
      { timeUnit: 'month', count: 1 }
    ])
    dateAxis.renderer.minGridDistance = 60
    dateAxis.renderer.grid.template.location = 0
    dateAxis.renderer.grid.template.disabled = true

    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis.cursorTooltipEnabled = false

    chart.legend = new am4charts.Legend()

    chart.cursor = new am4charts.XYCursor()
    chart.cursor.fullWidthLineX = true
    chart.cursor.xAxis = dateAxis
    chart.cursor.lineX.strokeOpacity = 0
    chart.cursor.lineX.fill = am4core.color('#000')
    chart.cursor.lineX.fillOpacity = 0.1
    chart.cursor.lineY.strokeOpacity = 0

    var series = chart.series.push(new am4charts.ColumnSeries())
    series.name = 'Приход'
    series.dataFields.valueY = 'income'
    series.dataFields.dateX = 'date'
    series.groupFields.valueY = 'sum'
    series.stroke = am4core.color('#19ffa3')
    series.columns.template.stroke = am4core.color('#19ffa3')
    series.columns.template.fill = am4core.color('#19ffa3')
    series.columns.template.fillOpacity = 0.5

    var series2 = chart.series.push(new am4charts.ColumnSeries())
    series2.name = 'Расход'
    series2.dataFields.valueY = 'expense'
    series2.dataFields.dateX = 'date'
    series2.groupFields.valueY = 'sum'
    series2.stroke = am4core.color('#ff604a')
    series2.columns.template.stroke = am4core.color('#ff604a')
    series2.columns.template.fill = am4core.color('#ff604a')
    series2.columns.template.fillOpacity = 0.5

    var series3 = chart.series.push(new am4charts.LineSeries())
    series3.name = 'Баланс'
    series3.dataFields.valueY = 'balance'
    series3.dataFields.dateX = 'date'
    series3.groupFields.valueY = 'sum'
    series3.stroke = am4core.color('#000')
    series3.fill = am4core.color('#19ffa3')
    series3.strokeWidth = 3
    series3.fillOpacity = 0.3
    series3.strokeOpacity = 0.8

    series3.adapter.add('tooltipHTML', (ev) => {
      var text = '<div class="mb-2"><strong>{dateX.formatDate(\'d MMMM yyyy\')}</strong></div>'
      chart.series.each((item) => {
        text += '<div class="text-sm"><span style="color:' + item.stroke.hex + '">●</span> ' + item.name + ': ' + this.$numeral(item.tooltipDataItem.valueY).format('0,0 $') + '</div>'
      })
      text += '<button class="bg-blue-500 hover:bg-blue-700 text-sm text-white font-bold py-2 px-4 rounded my-2">Подробнее</a>'
      return text
    })
    series3.tooltip.getFillFromObject = false
    series3.tooltip.background.filters.clear()
    series3.tooltip.background.fill = am4core.color('#000')
    series3.tooltip.background.fillOpacity = 0.8
    series3.tooltip.background.strokeWidth = 0
    series3.tooltip.background.cornerRadius = 1
    series3.tooltip.label.interactionsEnabled = true
    series3.tooltip.pointerOrientation = 'vertical'

    // Create a range to change stroke for values below 0
    const range = valueAxis.createSeriesRange(series3)
    range.value = 0
    range.endValue = -10000000
    range.contents.stroke = am4core.color('#ff604a')
    range.contents.fill = range.contents.stroke
    range.contents.strokeOpacity = 0.7
    range.contents.fillOpacity = 0.1

    const scrollbarX = new am4charts.XYChartScrollbar()
    scrollbarX.series.push(series3)
    scrollbarX.marginBottom = 20
    chart.scrollbarX = scrollbarX
    chart.scrollbarX.scrollbarChart.plotContainer.filters.clear()

    var scrollSeries1 = chart.scrollbarX.scrollbarChart.series.getIndex(0)
    scrollSeries1.strokeWidth = 1
    scrollSeries1.strokeOpacity = 0.4
    scrollSeries1.fillOpacity = 0

    var scrollAxisX = chart.scrollbarX.scrollbarChart.yAxes.getIndex(0)
    var range2 = scrollAxisX.createSeriesRange(chart.scrollbarX.scrollbarChart.series.getIndex(0))
    range2.value = 0
    range2.endValue = -10000000
    range2.contents.stroke = '#ff604a'
    range2.contents.fill = '#ff604a'

    this.chart = chart
  },

  methods: {
    renderChart () {
      this.chart.data = this.listItems
    }
  }

}
</script>

<style>
#chartdiv {
  width: 100%;
  height: 500px;
  margin-bottom: 4rem;
}

</style>

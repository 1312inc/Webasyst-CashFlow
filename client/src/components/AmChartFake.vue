<template>
    <div>

        <!-- <div>
          <button @click="getList({ from: '2020-06-01', to: '2020-09-01' })">90 дней</button>
        </div> -->

        <div id="chartdiv"></div>
    </div>
</template>

<script>

import { mapState, mapActions } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themesAnimated from '@amcharts/amcharts4/themes/animated'

import dataByDay from '@/plugins/dumpDataMonth'
am4core.useTheme(am4themesAnimated)

export default {

  data () {
    return {
      chartGroup: 'month'
    }
  },

  watch: {
    listItems () {
      this.renderChart()
    }
  },

  computed: {
    ...mapState('transaction', {
      listItems: state => state.fakeData
    }),

    categoriesInData () {
      return this.listItems.reduce((categories, item) => {
        if (!categories.includes(item.category_id)) categories.push(item.category_id)
        return categories
      }, [])
    }
  },

  mounted () {
    const chart = am4core.create('chartdiv', am4charts.XYChart)

    // Create axes
    const dateAxis = chart.xAxes.push(new am4charts.DateAxis())
    // dateAxis.groupData = true
    // dateAxis.groupCount = 180
    // dateAxis.groupIntervals.setAll([
    //   { timeUnit: 'day', count: 1 },
    //   { timeUnit: 'month', count: 1 }
    // ])
    dateAxis.renderer.minGridDistance = 60
    dateAxis.renderer.grid.template.location = 0
    dateAxis.renderer.grid.template.disabled = true
    // dateAxis.renderer.fullWidthTooltip = true

    dateAxis.events.on('selectionextremeschanged', ({ target }) => {
      const start = this.$moment(target.positionToDate(target.start))
      const end = this.$moment(target.positionToDate(target.end))
      if (end.diff(start) < 15552000000 && this.chartGroup === 'month') {
        this.chartGroup = 'day'
        this.chart.data = [...dataByDay].filter(e => {
          return this.$moment(e.date) <= end && this.$moment(e.date) >= start
        }).reverse()
        target.zoomToDates(start.format(), end.format())
      }
    })

    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis.cursorTooltipEnabled = false

    chart.legend = new am4charts.Legend()

    chart.cursor = new am4charts.XYCursor()
    // chart.cursor.maxTooltipDistance = 0
    chart.cursor.fullWidthLineX = true
    chart.cursor.xAxis = dateAxis
    chart.cursor.lineX.strokeOpacity = 0
    chart.cursor.lineX.fill = am4core.color('#000')
    chart.cursor.lineX.fillOpacity = 0.1
    chart.cursor.lineY.strokeOpacity = 0
    // Set up cursor's events to update the label
    chart.cursor.events.on('cursorpositionchanged', ({ target }) => {
      chart.series.each(function (series) {
        var dataItem = dateAxis.getSeriesDataItem(
          series,
          dateAxis.toAxisPosition(chart.cursor.xPosition),
          true
        )
        info.moveTo({ x: target.point.x - info.width / 2, y: 0 })
        updateValues(dataItem, series.name)
      })
    })

    // Updates values
    function updateValues (dataItem, key) {
      chart.series.each(function (series) {
        var label = chart.map.getKey(key)
        label.text = chart.numberFormatter.format(dataItem.valueY)
      })
    }

    var series = chart.series.push(new am4charts.ColumnSeries())
    series.name = 'Income'
    series.dataFields.valueY = 'income'
    series.dataFields.dateX = 'date'
    series.groupFields.valueY = 'sum'
    series.columns.template.column.fill = '#19ffa3'
    series.columns.template.column.strokeWidth = 0
    // series.columns.template.tooltipHTML = '<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>'

    var series2 = chart.series.push(new am4charts.ColumnSeries())
    series2.name = 'Expense'
    series2.dataFields.valueY = 'expense'
    series2.dataFields.dateX = 'date'
    series2.groupFields.valueY = 'sum'
    series2.columns.template.column.fill = '#ff604a'
    series2.columns.template.column.strokeWidth = 0
    // series2.columns.template.tooltipHTML = '<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>'

    var series3 = chart.series.push(new am4charts.LineSeries())
    series3.name = 'Balance'
    series3.dataFields.valueY = 'balance'
    series3.dataFields.dateX = 'date'
    series3.groupFields.valueY = 'sum'
    series3.strokeWidth = 2
    series3.fillOpacity = 0.1

    // Create a range to change stroke for values below 0
    const range = valueAxis.createSeriesRange(series3)
    range.value = 0
    range.endValue = -10000000
    range.contents.stroke = chart.colors.getIndex(4)
    range.contents.fill = range.contents.stroke
    range.contents.strokeOpacity = 0.7
    range.contents.fillOpacity = 0.1

    const scrollbarX = new am4charts.XYChartScrollbar()
    scrollbarX.series.push(series3)
    scrollbarX.marginBottom = 20
    chart.scrollbarX = scrollbarX

    // Create container to hold our hover labels
    var info = chart.plotContainer.createChild(am4core.Container)
    info.width = 220
    info.x = 0
    info.y = 0
    info.padding(10, 10, 10, 10)
    info.background.fill = am4core.color('#000')
    info.background.fillOpacity = 0.8
    info.layout = 'grid'
    info.zIndex = 10000

    var titleContainer = info.createChild(am4core.Container)
    titleContainer.width = 200
    titleContainer.layout = 'grid'

    var detailsContainer = info.createChild(am4core.Container)
    detailsContainer.width = 200
    detailsContainer.layout = 'grid'

    var buttonContainer = info.createChild(am4core.Container)
    buttonContainer.width = 200

    var tooltipTitle = titleContainer.createChild(am4core.Label)
    tooltipTitle.text = ''
    tooltipTitle.fill = '#fff'
    tooltipTitle.marginBottom = 5

    var button = buttonContainer.createChild(am4core.Button)
    button.label.text = 'Подробнее'
    button.align = 'center'
    button.marginTop = 10
    button.events.on('hit', () => {
      alert('Показ детализации за указанный период')
    })

    // Create labels
    function createLabel (field, title, color) {
      var titleLabel = detailsContainer.createChild(am4core.Label)
      titleLabel.text = title + ':'
      titleLabel.marginRight = 5
      titleLabel.minWidth = 100
      titleLabel.fill = color

      var valueLabel = detailsContainer.createChild(am4core.Label)
      valueLabel.id = title
      valueLabel.text = ''
      valueLabel.minWidth = 50
      valueLabel.fontWeight = 'bolder'
      valueLabel.fill = color
    }

    chart.series.each((series) => {
      createLabel(series.dataFields.valueY, series.name, series.stroke)
    })

    this.chart = chart
  },

  methods: {
    ...mapActions(['getList']),

    renderChart () {
      this.chart.data = [...this.listItems].reverse()
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

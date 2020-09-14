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
    dateAxis.groupData = true
    dateAxis.groupCount = 180
    dateAxis.groupIntervals.setAll([
      { timeUnit: 'day', count: 1 },
      { timeUnit: 'month', count: 1 }
    ])
    dateAxis.renderer.minGridDistance = 60
    // dateAxis.renderer.grid.template.location = 0
    // dateAxis.renderer.grid.template.disabled = true
    dateAxis.renderer.fullWidthTooltip = true

    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis.cursorTooltipEnabled = false

    chart.legend = new am4charts.Legend()

    chart.cursor = new am4charts.XYCursor()
    chart.cursor.maxTooltipDistance = 0
    chart.cursor.fullWidthLineX = true
    chart.cursor.xAxis = dateAxis
    chart.cursor.lineX.strokeOpacity = 0
    chart.cursor.lineX.fill = am4core.color('#000')
    chart.cursor.lineX.fillOpacity = 0.1
    chart.cursor.lineY.strokeOpacity = 0
    // Set up cursor's events to update the label
    // chart.cursor.events.on('cursorpositionchanged', function (ev) {
    //   chart.series.each(function (series) {
    //     var dataItem = dateAxis.getSeriesDataItem(
    //       series,
    //       dateAxis.toAxisPosition(chart.cursor.xPosition),
    //       true
    //     )
    //     console.log(dataItem.name)
    //     console.log(dataItem)
    //     updateValues(dataItem, series.name)
    //   })
    // })

    // Updates values
    // function updateValues (dataItem, key) {
    //   chart.series.each(function (series) {
    //     var label = chart.map.getKey(key)
    //     label.text = chart.numberFormatter.format(dataItem.valueY)
    //   })
    // }

    const scrollbarX = new am4core.Scrollbar()
    // scrollbarX.marginBottom = 20
    chart.scrollbarX = scrollbarX

    this.chart = chart
  },

  methods: {
    ...mapActions(['getList']),

    renderChart () {
      this.chart.data = [...this.listItems].reverse()

      var series = this.chart.series.push(new am4charts.ColumnSeries())
      series.name = 'Income'
      series.dataFields.valueY = 'income'
      series.dataFields.dateX = 'date'
      series.groupFields.valueY = 'sum'
      series.columns.template.column.fill = '#19ffa3'
      series.columns.template.column.strokeWidth = 0
      series.columns.template.tooltipHTML = '<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>'
      //   series.adapter.add('tooltipText', (ev) => {
      //     var text = '[bold]{dateX}[/]\n'
      //     this.chart.series.each(function (item) {
      //       console.log(item.name)
      //       console.log(item)
      //       text += '[' + item.stroke.hex + ']●[/] ' + item.name + ': {valueY}\n'
      //     })
      //     return text
      //   })

      var series2 = this.chart.series.push(new am4charts.ColumnSeries())
      series2.name = 'Expense'
      series2.dataFields.valueY = 'expense'
      series2.dataFields.dateX = 'date'
      series2.groupFields.valueY = 'sum'
      series2.columns.template.column.fill = '#ff604a'
      series2.columns.template.column.strokeWidth = 0
      series2.columns.template.tooltipHTML = '<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>'

      //   var info = this.chart.plotContainer.createChild(am4core.Container)
      //   info.width = 320
      //   info.height = 60
      //   info.x = 10
      //   info.y = 10
      //   info.padding(10, 10, 10, 10)
      //   info.background.fill = am4core.color('#000')
      //   info.background.fillOpacity = 0.1
      //   info.layout = 'grid'

      //   // Create labels
      //   function createLabel (field, title, color) {
      //     var titleLabel = info.createChild(am4core.Label)
      //     titleLabel.text = title + ':'
      //     titleLabel.marginRight = 5
      //     titleLabel.minWidth = 100
      //     titleLabel.fill = color

      //     var valueLabel = info.createChild(am4core.Label)
      //     valueLabel.id = title
      //     valueLabel.text = '-'
      //     valueLabel.minWidth = 50
      //     valueLabel.fontWeight = 'bolder'
      //     valueLabel.fill = color
      //   }

      //   this.chart.series.each(function (series) {
      //     createLabel(series.dataFields.valueY, series.name, series.stroke)
      //   })
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

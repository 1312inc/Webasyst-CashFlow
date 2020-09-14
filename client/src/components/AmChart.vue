<template>
    <div>

        <div>
          <button @click="getList({ from: '2020-06-01', to: '2020-09-01' })">90 дней</button>
        </div>

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
    ...mapState('transaction', ['listItems']),

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
    dateAxis.groupInterval = { timeUnit: 'day', count: 1 }
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

    const scrollbarX = new am4core.Scrollbar()
    scrollbarX.marginBottom = 20
    chart.scrollbarX = scrollbarX
    // chart.scrollbarX.events.on('hit', () => {
    //   alert('d')
    // })

    this.chart = chart
  },

  methods: {
    ...mapActions(['getList']),

    renderChart () {
      [...this.chart.series.values].forEach((series, i) => {
        if (!this.categoriesInData.includes(series.name)) {
          console.log(`detele serial ${series.name} ${i}`)
          this.chart.series.removeIndex(
            this.chart.series.indexOf(series)
          ).dispose()
        }
      })

      for (const categoryId of this.categoriesInData) {
        const series = this.chart.series.values.find(series => series.name === categoryId) || this.chart.series.push(new am4charts.ColumnSeries())

        if (!series.name) {
          series.name = categoryId
          series.dataFields.valueY = 'amount'
          series.dataFields.dateX = 'date'
          // series.stacked = true
          series.tooltipText = '[bold]{name}[/]\n[font-size:14px]{dateX}: {valueY}'

          // Set up tooltip
          // series.adapter.add('tooltipText', (ev) => {
          //   var text = '[bold]{dateX}[/]\n'
          //   this.chart.series.each(function (item) {
          //     text += '[' + item.stroke.hex + ']●[/] ' + item.name + ': {' + item.dataFields.valueY + '}\n'
          //   })
          //   return text
          // })

          // series.tooltip.getFillFromObject = false
          // series.tooltip.background.fill = am4core.color('#fff')
          // series.tooltip.label.fill = am4core.color('#00')

          console.log(`craete serial ${categoryId}`)
        } else {
          console.log(`update serial ${categoryId}`)
        }

        series.data = this.listItems.filter(e => {
          return e.category_id === categoryId
        }).map(e => {
          e.amount = Math.abs(e.amount)
          return e
        }).reverse()
      }
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

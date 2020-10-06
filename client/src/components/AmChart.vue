<template>
    <div class="mt-10">
      <div class="flex justify-between">
        <div>
          <ChartHeader></ChartHeader>
          <div v-if="dates.from" class="text-xl">
            {{$moment(dates.from).format("LL")}} – {{$moment(dates.to).format("LL")}}
          </div>
        </div>
        <div class="flex justify-end">
          <div class="w-64 mr-4">
            <Dropdown :items=pastIntervals :defaultSelectedIndex=0 title="Past" @selected="setIntervalFrom" />
          </div>
          <div class="w-64">
            <Dropdown :items=futureIntervals :defaultSelectedIndex=0 title="Future" @selected="setIntervalTo" />
          </div>
        </div>
      </div>

        <div id="chartdiv"></div>
    </div>
</template>

<script>

import { mapState, mapActions } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themesAnimated from '@amcharts/amcharts4/themes/animated'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'
import ChartHeader from '@/components/ChartHeader'
import Dropdown from '@/components/Dropdown'

am4core.useTheme(am4themesAnimated)

export default {

  components: {
    ChartHeader,
    Dropdown
  },

  data () {
    return {
      dates: {
        from: '',
        to: ''
      },
      pastIntervals: [
        {
          title: 'Last 30 days',
          value: this.setIntervalDate(-1, 'M')
        },
        {
          title: 'Last 90 days',
          value: this.setIntervalDate(-3, 'M')
        },
        {
          title: 'Last 180 days',
          value: this.setIntervalDate(-6, 'M')
        },
        {
          title: 'Last 365 days',
          value: this.setIntervalDate(-1, 'Y')
        },
        {
          title: 'Last 3 years',
          value: this.setIntervalDate(-3, 'Y')
        },
        {
          title: 'Last 5 years',
          value: this.setIntervalDate(-5, 'Y')
        },
        {
          title: 'Last 10 years',
          value: this.setIntervalDate(-10, 'Y')
        }

      ],
      futureIntervals: [
        {
          title: 'None',
          value: this.setIntervalDate(0, 'd')
        },
        {
          title: 'Last 30 days',
          value: this.setIntervalDate(1, 'M')
        },
        {
          title: 'Last 90 days',
          value: this.setIntervalDate(3, 'M')
        },
        {
          title: 'Last 180 days',
          value: this.setIntervalDate(6, 'M')
        },
        {
          title: 'Last 365 days',
          value: this.setIntervalDate(1, 'Y')
        },
        {
          title: 'Last 2 years',
          value: this.setIntervalDate(2, 'Y')
        },
        {
          title: 'Last 3 years',
          value: this.setIntervalDate(3, 'Y')
        }
      ]
    }
  },

  watch: {
    chartData () {
      this.renderChart()
    }
  },

  computed: {
    ...mapState('transaction', ['chartData'])
  },

  mounted () {
    const chart = am4core.create('chartdiv', am4charts.XYChart)
    chart.language.locale = am4langRU

    chart.leftAxesContainer.layout = 'vertical'
    chart.seriesContainer.zIndex = -1

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

    const dateAxisChanged = ({ target }) => {
      setTimeout(() => {
        if (!target.isInTransition()) {
          this.dates.from = this.$moment(target.minZoomed).format('YYYY-MM-DD')
          this.dates.to = this.$moment(target.maxZoomed).format('YYYY-MM-DD')
          this.setdetailsInterval({ from: this.dates.from, to: this.dates.to })
        }
      }, 0)
    }

    dateAxis.events.on('startchanged', dateAxisChanged)
    dateAxis.events.on('endchanged', dateAxisChanged)
    dateAxis.events.on('datarangechanged', ({ target }) => {
      this.dates.from = this.$moment(target.minZoomed).format('YYYY-MM-DD')
      this.dates.to = this.$moment(target.maxZoomed).format('YYYY-MM-DD')
    })

    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis.cursorTooltipEnabled = false
    valueAxis.zIndex = 1
    valueAxis.height = am4core.percent(35)
    valueAxis.renderer.grid.template.disabled = true
    valueAxis.renderer.labels.template.disabled = true

    const valueAxis2 = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis2.cursorTooltipEnabled = false
    valueAxis2.zIndex = 3
    valueAxis2.height = am4core.percent(65)
    valueAxis2.marginTop = 30
    valueAxis2.renderer.gridContainer.background.fill = am4core.color('#000000')
    valueAxis2.renderer.gridContainer.background.fillOpacity = 0.01

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
    series.yAxis = valueAxis2
    series.dataFields.valueY = 'income'
    series.dataFields.dateX = 'date'
    series.groupFields.valueY = 'sum'
    series.stroke = am4core.color('#19ffa3')
    series.columns.template.stroke = am4core.color('#19ffa3')
    series.columns.template.fill = am4core.color('#19ffa3')
    series.columns.template.fillOpacity = 0.5
    series.defaultState.transitionDuration = 0

    var series2 = chart.series.push(new am4charts.ColumnSeries())
    series2.name = 'Расход'
    series2.yAxis = valueAxis2
    series2.dataFields.valueY = 'expense'
    series2.dataFields.dateX = 'date'
    series2.groupFields.valueY = 'sum'
    series2.stroke = am4core.color('#ff604a')
    series2.columns.template.stroke = am4core.color('#ff604a')
    series2.columns.template.fill = am4core.color('#ff604a')
    series2.columns.template.fillOpacity = 0.5
    series2.defaultState.transitionDuration = 0

    var series3 = chart.series.push(new am4charts.LineSeries())
    series3.name = 'Баланс'
    series3.yAxis = valueAxis
    series3.dataFields.valueY = 'balance'
    series3.dataFields.dateX = 'date'
    series3.groupFields.valueY = 'sum'
    series3.stroke = am4core.color('#19ffa3')
    series3.strokeWidth = 3
    series3.strokeOpacity = 0.8
    series3.defaultState.transitionDuration = 0

    series3.adapter.add('tooltipHTML', (ev) => {
      var text = '<div class="p-2">'
      text += '<div class="mb-4"><strong>{dateX.formatDate(\'d MMMM yyyy\')}</strong></div>'
      let timeUnit
      chart.series.each((item, i) => {
        text += '<div class="text-sm mb-2"><span style="color:' + item.stroke.hex + '">●</span> ' + item.name + ': ' + this.$numeral(item.tooltipDataItem.valueY).format('0,0 $') + '</div>'
        if (i === 2) {
          timeUnit = item.tooltipDataItem.groupDataItems ? 'month' : 'day'
        }
      })

      text += '<button onclick="toggleDateForDetails(\'{dateX}\', \'' + timeUnit + '\')" class="bg-blue-500 hover:bg-blue-700 text-sm text-white font-bold py-2 px-4 rounded my-2">Подробнее</a>'
      text += '</div>'
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
    range.contents.strokeOpacity = 0.7

    // Currend day line
    const dateBorder = dateAxis.axisRanges.create()
    dateBorder.date = new Date()
    dateBorder.grid.stroke = am4core.color('#000000')
    dateBorder.grid.strokeWidth = 1
    dateBorder.grid.strokeOpacity = 0.6

    // Future dates hover
    const range3 = dateAxis.axisRanges.create()
    range3.date = new Date()
    range3.endDate = new Date(2100, 0, 3)
    range3.grid.disabled = true
    range3.axisFill.fillOpacity = 0.6
    range3.axisFill.fill = '#FFFFFF'

    const scrollbarX = new am4charts.XYChartScrollbar()
    scrollbarX.series.push(series3)
    scrollbarX.marginTop = 20
    scrollbarX.marginBottom = 20
    chart.scrollbarX = scrollbarX
    chart.scrollbarX.scrollbarChart.plotContainer.filters.clear()
    chart.scrollbarX.parent = chart.bottomAxesContainer

    var scrollSeries1 = chart.scrollbarX.scrollbarChart.series.getIndex(0)
    scrollSeries1.strokeWidth = 1
    scrollSeries1.strokeOpacity = 0.4

    var scrollAxisX = chart.scrollbarX.scrollbarChart.yAxes.getIndex(0)
    var range2 = scrollAxisX.createSeriesRange(chart.scrollbarX.scrollbarChart.series.getIndex(0))
    range2.value = 0
    range2.endValue = -10000000
    range2.contents.stroke = '#ff604a'
    range2.contents.fill = '#ff604a'

    this.chart = chart

    window.toggleDateForDetails = (date, interval) => {
      const from = date
      let to = date
      if (interval === 'month') {
        to = this.$moment(from).add(1, 'M').format('YYYY-MM-DD')
      }
      this.setdetailsInterval({ from, to })
    }
  },

  methods: {
    ...mapActions('transaction', ['setdetailsInterval']),

    renderChart () {
      this.chart.data = this.chartData
    },

    setIntervalFrom (index, value) {
      this.$store.dispatch('transaction/resetAllDataToInterval', { from: value })
    },

    setIntervalTo (index, value) {
      this.$store.dispatch('transaction/resetAllDataToInterval', { to: value })
    },

    setIntervalDate (days, interval) {
      return this.$moment().add(days, interval).format('YYYY-MM-DD')
    }
  }

}
</script>

<style>
#chartdiv {
  width: 100%;
  height: 600px;
  margin-bottom: 2rem;
}

</style>

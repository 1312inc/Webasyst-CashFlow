<template>
    <div>
      <div class="flexbox custom-mb-12">
        <div class="wide">
          <ChartHeader></ChartHeader>
        </div>
        <div class="flexbox space-1rem">
          <div>
            <Dropdown type="from" />
          </div>
          <div>
            <Dropdown type="to" />
          </div>
        </div>
      </div>

      <div class="chart-container">
        <div v-if="loading" class="skeleton-container">
          <div class="skeleton">
            <span class="skeleton-custom-box"></span>
          </div>
        </div>
        <div id="chartdiv" class="smaller"></div>
      </div>

    </div>
</template>

<script>
import { locale } from '@/plugins/locale'
import { mapState, mapActions } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'
import ChartHeader from '@/components/ChartHeader'
import Dropdown from '@/components/Dropdown'

export default {

  components: {
    ChartHeader,
    Dropdown
  },

  computed: {
    ...mapState('transaction', ['chartData', 'loading'])
  },

  watch: {
    chartData () {
      this.renderChart()
    }
  },

  mounted () {
    const chart = am4core.create('chartdiv', am4charts.XYChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU

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
    valueAxis2.renderer.grid.template.strokeOpacity = 0.09
    valueAxis2.renderer.grid.template.strokeWidth = 1

    chart.legend = new am4charts.Legend()

    chart.cursor = new am4charts.XYCursor()
    chart.cursor.fullWidthLineX = true
    chart.cursor.xAxis = dateAxis
    chart.cursor.lineX.strokeOpacity = 0
    chart.cursor.lineX.fill = am4core.color('#000')
    chart.cursor.lineX.fillOpacity = 0.1
    chart.cursor.lineY.strokeOpacity = 0
    chart.cursor.behavior = 'none'

    var series = chart.series.push(new am4charts.ColumnSeries())
    series.name = this.$t('income')
    series.yAxis = valueAxis2
    series.dataFields.valueY = 'amountIncome'
    series.dataFields.dateX = 'period'
    series.groupFields.valueY = 'sum'
    series.stroke = am4core.color('#19ffa3')
    series.columns.template.stroke = am4core.color('#19ffa3')
    series.columns.template.fill = am4core.color('#19ffa3')
    series.columns.template.fillOpacity = 0.5
    series.defaultState.transitionDuration = 0

    var series2 = chart.series.push(new am4charts.ColumnSeries())
    series2.name = this.$t('expense')
    series2.yAxis = valueAxis2
    series2.dataFields.valueY = 'amountExpense'
    series2.dataFields.dateX = 'period'
    series2.groupFields.valueY = 'sum'
    series2.stroke = am4core.color('#ff604a')
    series2.columns.template.stroke = am4core.color('#ff604a')
    series2.columns.template.fill = am4core.color('#ff604a')
    series2.columns.template.fillOpacity = 0.5
    series2.defaultState.transitionDuration = 0

    var series3 = chart.series.push(new am4charts.LineSeries())
    series3.name = this.$t('balance')
    series3.yAxis = valueAxis
    series3.dataFields.valueY = 'balance'
    series3.dataFields.dateX = 'period'
    series3.groupFields.valueY = 'sum'
    series3.stroke = am4core.color('#19ffa3')
    series3.strokeWidth = 3
    series3.strokeOpacity = 0.8
    series3.defaultState.transitionDuration = 0

    series3.adapter.add('tooltipHTML', (ev) => {
      var text = '<div>'
      text += '<div class="custom-my-8"><strong>{dateX.formatDate(\'d MMMM yyyy\')}</strong></div>'
      let timeUnit
      chart.series.each((item, i) => {
        text += '<div class="custom-mb-4"><span style="color:' + item.stroke.hex + '">●</span> ' + item.name + ': ' + this.$numeral(item.tooltipDataItem.valueY).format() + '</div>'
        if (i === 2) {
          timeUnit = item.tooltipDataItem.groupDataItems ? 'month' : 'day'
        }
      })

      text += '<button onclick="toggleDateForDetails(\'{dateX}\', \'' + timeUnit + '\')" class="button small custom-my-8">' + this.$t('details') + '</button>'
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
    dateBorder.grid.stroke = am4core.color('#333333')
    dateBorder.grid.strokeWidth = 1
    dateBorder.grid.strokeOpacity = 0.6
    dateBorder.label.inside = true
    dateBorder.label.valign = 'middle'
    dateBorder.label.text = this.$t('today')
    dateBorder.label.fill = dateBorder.grid.stroke
    dateBorder.label.rotation = -90
    dateBorder.label.verticalCenter = 'middle'
    dateBorder.label.dx = -10

    // Future dates hover
    const range3 = dateAxis.axisRanges.create()
    range3.date = new Date()
    range3.endDate = new Date(2100, 0, 3)
    range3.grid.disabled = true
    range3.axisFill.fillOpacity = 0.6
    range3.axisFill.fill = '#FFFFFF'

    const scrollbarX = new am4charts.XYChartScrollbar()
    scrollbarX.series.push(series3)
    chart.scrollbarX = scrollbarX
    chart.scrollbarX.scrollbarChart.plotContainer.filters.clear()
    chart.scrollbarX.parent = chart.bottomAxesContainer

    const dateAxisChanged = () => {
      const from = this.$moment(dateAxis.minZoomed).format('YYYY-MM-DD')
      const to = this.$moment(dateAxis.maxZoomed).format('YYYY-MM-DD')
      this.setdetailsInterval({ from, to })
    }

    chart.scrollbarX.startGrip.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.endGrip.events.on('dragstop', dateAxisChanged)

    var scrollSeries1 = chart.scrollbarX.scrollbarChart.series.getIndex(0)
    scrollSeries1.strokeWidth = 1
    scrollSeries1.strokeOpacity = 0.4

    var scrollAxisX = chart.scrollbarX.scrollbarChart.yAxes.getIndex(0)
    var range2 = scrollAxisX.createSeriesRange(chart.scrollbarX.scrollbarChart.series.getIndex(0))
    range2.value = 0
    range2.endValue = -10000000
    range2.contents.stroke = '#ff604a'
    range2.contents.fill = '#ff604a'

    /**
   * ========================================================
   * Enabling responsive features
   * ========================================================
   */

    chart.responsive.useDefault = false
    chart.responsive.enabled = true

    chart.responsive.rules.push({
      relevant: function (target) {
        if (target.pixelWidth <= 400) {
          return true
        }

        return false
      },
      state: function (target, stateId) {
        if (target instanceof am4charts.Chart) {
          var state = target.states.create(stateId)
          state.properties.paddingTop = 5
          state.properties.paddingRight = 0
          state.properties.paddingBottom = 0
          state.properties.paddingLeft = 0
          return state
        }

        if ((target instanceof am4charts.AxisLabel) && (target.parent instanceof am4charts.AxisRendererY)) {
          // eslint-disable-next-line no-redeclare
          var state = target.states.create(stateId)
          state.properties.inside = true
          state.properties.maxLabelPosition = 0.99
          return state
        }
        return null
      }
    })

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
      this.chart.xAxes.values[0].min = (new Date(this.$store.state.transaction.queryParams.from)).getTime()
      this.chart.xAxes.values[0].max = (new Date(this.$store.state.transaction.queryParams.to)).getTime()
    }

  }

}
</script>

<style lang="scss">
  .chart-container {
    position: relative;
    height: 600px;
    margin-bottom: 1rem;
    overflow: hidden;

    .skeleton-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #ffffff;
      z-index: 40;

      .skeleton {
        position: absolute;
        width: 100%;
        height: 100%;
      }

      .skeleton-custom-box {
        height: 100%;
      }
    }

    @media (max-width: 768px) {
      height: 400px !important;
    }
  }

  #chartdiv {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 30;
  }
</style>

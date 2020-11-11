<template>
    <div class="chart-container">
      <div id="chartdiv" class="smaller" :class="{'tw-opacity-0': loadingChart}"></div>
      <!-- <transition name="fade-appear"> -->
        <div v-if="loadingChart" class="skeleton-container">
          <div class="skeleton">
            <span class="skeleton-custom-box"></span>
          </div>
        </div>
      <!-- </transition> -->
    </div>
</template>

<script>
import { locale } from '@/plugins/locale'
import { mapState, mapMutations } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {

  computed: {
    ...mapState('transaction', ['chartData', 'loadingChart']),

    activeChartData () {
      return Array.isArray(this.chartData) ? this.chartData[0] : { data: [] }
    },

    showIncome () {
      return !!this.activeChartData.data[0]?.amountIncome
    },

    showExpense () {
      return !!this.activeChartData.data[0]?.amountExpense
    },

    showBalance () {
      return !!this.activeChartData.data[0]?.balance
    },

    currency () {
      return this.$store.getters['system/getCurrencySignByCode'](this.activeChartData.currency)
    }
  },

  watch: {
    activeChartData () {
      this.renderChart()
    }
  },

  mounted () {
    const chart = am4core.create('chartdiv', am4charts.XYChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU

    chart.leftAxesContainer.layout = 'vertical'

    // Date axis
    const dateAxis = chart.xAxes.push(new am4charts.DateAxis())
    // dateAxis.groupData = true
    // dateAxis.groupCount = 1000
    // dateAxis.groupIntervals.setAll([
    //   { timeUnit: 'day', count: 1 },
    //   { timeUnit: 'month', count: 1 }
    // ])
    // dateAxis.renderer.minGridDistance = 60
    dateAxis.renderer.grid.template.location = 0
    dateAxis.renderer.grid.template.disabled = true
    dateAxis.snapTooltip = false

    // Cols axis
    this.colsAxis = chart.yAxes.push(new am4charts.ValueAxis())
    this.colsAxis.cursorTooltipEnabled = false
    this.colsAxis.renderer.gridContainer.background.fill = am4core.color('#f3f3f3')
    this.colsAxis.renderer.gridContainer.background.fillOpacity = 0.3
    this.colsAxis.renderer.grid.template.strokeOpacity = 0.06

    // Legend
    chart.legend = new am4charts.Legend()

    // Cursor
    chart.cursor = new am4charts.XYCursor()
    chart.cursor.fullWidthLineX = true
    chart.cursor.xAxis = dateAxis
    chart.cursor.lineX.strokeOpacity = 0
    chart.cursor.lineX.fill = am4core.color('#000')
    chart.cursor.lineX.fillOpacity = 0.1
    chart.cursor.lineY.strokeOpacity = 0
    chart.cursor.behavior = 'none'

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
    const rangeFututre = dateAxis.axisRanges.create()
    rangeFututre.date = new Date()
    rangeFututre.endDate = new Date(2100, 0, 3)
    rangeFututre.grid.disabled = true
    rangeFututre.axisFill.fillOpacity = 0.6
    rangeFututre.axisFill.fill = '#FFFFFF'

    // Scrollbar on the bottom
    chart.scrollbarX = new am4core.Scrollbar()
    chart.scrollbarX.parent = chart.bottomAxesContainer
    chart.scrollbarX.background.fill = am4core.color('#f3f3f3')
    chart.scrollbarX.thumb.background.fill = am4core.color('#f3f3f3')
    chart.scrollbarX.stroke = am4core.color('#f3f3f3')

    const dateAxisChanged = () => {
      const from = this.$moment(dateAxis.minZoomed).format('YYYY-MM-DD')
      const to = this.$moment(dateAxis.maxZoomed).format('YYYY-MM-DD')
      this.setDetailsInterval({ from, to })
    }

    chart.scrollbarX.startGrip.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.endGrip.events.on('dragstop', dateAxisChanged)

    chart.zoomOutButton.events.on('hit', () => {
      this.setDetailsInterval({ from: '', to: '' })
      this.$store.commit('transaction/updateQueryParams', { offset: 0 })
    })

    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/setDetailsInterval') {
        if (mutation.payload.from === '') {
          dateAxis.zoom({ start: 0, end: 1 })
        }
      }
    })

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
      this.setDetailsInterval({ from, to })
    }
  },

  beforeDestroy () {
    this.unsubscribe()
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    ...mapMutations('transaction', ['setDetailsInterval']),

    renderChart () {
      this.removeSeries(this.incomeSeries)
      this.removeSeries(this.expenseSeries)
      this.removeSeries(this.balanceSeries)
      if (this.balanceAxis) {
        const i = this.chart.yAxes.indexOf(this.balanceAxis)
        if (i > -1) this.chart.yAxes.removeIndex(i).dispose()
      }

      if (this.showIncome) this.addIncomeSeries()
      if (this.showExpense) this.addExpenseSeries()
      if (this.showBalance) {
        this.colsAxis.height = am4core.percent(65)
        this.addBalanceSeries()
      } else {
        this.colsAxis.height = am4core.percent(100)
      }

      this.chart.data = this.activeChartData.data
      this.chart.xAxes.values[0].min = (new Date(this.$store.state.transaction.queryParams.from)).getTime()
      this.chart.xAxes.values[0].max = (new Date(this.$store.state.transaction.queryParams.to)).getTime()
    },

    addIncomeSeries () {
      this.incomeSeries = this.chart.series.push(new am4charts.ColumnSeries())
      this.incomeSeries.name = this.$t('income')
      this.incomeSeries.yAxis = this.colsAxis
      this.incomeSeries.dataFields.valueY = 'amountIncome'
      this.incomeSeries.dataFields.dateX = 'period'
      this.incomeSeries.groupFields.valueY = 'sum'
      this.incomeSeries.stroke = am4core.color('#19ffa3')
      this.incomeSeries.columns.template.stroke = am4core.color('#19ffa3')
      this.incomeSeries.columns.template.fill = am4core.color('#19ffa3')
      this.incomeSeries.columns.template.fillOpacity = 0.5
      this.incomeSeries.defaultState.transitionDuration = 0

      if (!this.showBalance) this.attacheTooltip(this.incomeSeries)
    },

    addExpenseSeries () {
      this.expenseSeries = this.chart.series.push(new am4charts.ColumnSeries())
      this.expenseSeries.name = this.$t('expense')
      this.expenseSeries.yAxis = this.colsAxis
      this.expenseSeries.dataFields.valueY = 'amountExpense'
      this.expenseSeries.dataFields.dateX = 'period'
      this.expenseSeries.groupFields.valueY = 'sum'
      this.expenseSeries.stroke = am4core.color('#ff604a')
      this.expenseSeries.columns.template.stroke = am4core.color('#ff604a')
      this.expenseSeries.columns.template.fill = am4core.color('#ff604a')
      this.expenseSeries.columns.template.fillOpacity = 0.5
      this.expenseSeries.defaultState.transitionDuration = 0

      if (!this.showBalance) this.attacheTooltip(this.expenseSeries)
    },

    addBalanceSeries () {
      this.addBalanceAxis().events.on('ready', () => {
        this.balanceSeries = this.chart.series.push(new am4charts.LineSeries())
        this.balanceSeries.name = this.$t('balance')
        this.balanceSeries.yAxis = this.balanceAxis
        this.balanceSeries.dataFields.valueY = 'balance'
        this.balanceSeries.dataFields.dateX = 'period'
        this.balanceSeries.groupFields.valueY = 'sum'
        this.balanceSeries.stroke = am4core.color('#19ffa3')
        this.balanceSeries.strokeWidth = 3
        this.balanceSeries.strokeOpacity = 0.8
        this.balanceSeries.defaultState.transitionDuration = 0

        this.attacheTooltip(this.balanceSeries)

        // Create a range to change stroke for values below 0
        const range = this.balanceAxis.createSeriesRange(this.balanceSeries)
        range.value = 0
        range.endValue = -10000000
        range.contents.stroke = am4core.color('#ff604a')
        range.contents.strokeOpacity = 0.7
      })
    },

    addBalanceAxis () {
      this.balanceAxis = this.chart.yAxes.push(new am4charts.ValueAxis())
      this.balanceAxis.cursorTooltipEnabled = false
      this.balanceAxis.height = am4core.percent(35)
      this.balanceAxis.marginBottom = 30
      this.balanceAxis.renderer.gridContainer.background.fill = am4core.color('#f3f3f3')
      this.balanceAxis.renderer.gridContainer.background.fillOpacity = 0.3
      this.balanceAxis.renderer.grid.template.strokeOpacity = 0.06
      this.balanceAxis.insertBefore(this.colsAxis)

      return this.balanceAxis
    },

    removeSeries (seriesToRemove) {
      const i = this.chart.series.indexOf(seriesToRemove)
      if (i > -1) this.chart.series.removeIndex(i).dispose()
    },

    attacheTooltip (series) {
      series.adapter.add('tooltipHTML', () => {
        let text = '<div>'
        text += '<div class="custom-my-4"><strong>{dateX.formatDate(\'d MMMM yyyy\')}</strong></div>'
        let timeUnit
        this.chart.series.each((item, i) => {
          text += `<div class="custom-mb-2"><span style="color: ${item.stroke.hex}">●</span> ${item.name}: ${this.$numeral(item.tooltipDataItem.valueY).format()} ${this.currency}</div>`
          if (i === 2) {
            timeUnit = item.tooltipDataItem.groupDataItems ? 'month' : 'day'
          }
        })

        text += '<button onclick="toggleDateForDetails(\'{dateX}\', \'' + timeUnit + '\')" class="button small custom-my-8">' + this.$t('details') + '</button>'
        text += '</div>'
        return text
      })

      series.tooltip.getFillFromObject = false
      series.tooltip.background.filters.clear()
      series.tooltip.background.fill = am4core.color('#333')
      series.tooltip.background.fillOpacity = 1
      series.tooltip.background.strokeWidth = 0
      series.tooltip.background.cornerRadius = 3
      series.tooltip.label.interactionsEnabled = true
      series.tooltip.pointerOrientation = 'vertical'
    }

  }

}
</script>

<style lang="scss">
  .chart-container {
    position: relative;
    height: 500px;
    margin-bottom: 1rem;
    overflow: hidden;

    .skeleton-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;

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
  }
</style>

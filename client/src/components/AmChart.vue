<template>
  <div class="chart-container">
    <div v-if="isShowChart && isMultipleCurrencies" class="toggle">
      <span
        @click="activeCurrencyChart = i"
        v-for="(currencyData, i) in chartData"
        :key="i"
        :class="{ selected: i === activeCurrencyChart }"
      >
        {{ currencyData.currency }}
      </span>
    </div>
    <div>
      <div
        ref="chartdiv"
        class="chart-main smaller"
        :class="{ 'tw-opacity-0': !isShowChart }"
      ></div>
    </div>
    <!-- <transition name="fade-appear"> -->
    <div v-if="!isShowChart" class="skeleton-container">
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
import am4themesAnimated from '@amcharts/amcharts4/themes/animated'
import am4themesDark from '@amcharts/amcharts4/themes/amchartsdark'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

let prefersColorSchemeDark = false

am4core.useTheme(am4themesAnimated)
if (window?.appState?.theme === 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  prefersColorSchemeDark = true
  am4core.useTheme(am4themesDark)
}

export default {

  data () {
    return {
      dataValidated: true
    }
  },

  computed: {
    ...mapState('transaction', ['chartData', 'chartDataCurrencyIndex', 'loadingChart']),

    currentCategory () {
      return this.$store.getters.getCurrentType
    },

    isShowChart () {
      return !this.loadingChart && this.dataValidated
    },

    isMultipleCurrencies () {
      return Array.isArray(this.chartData) && this.chartData.length > 1
    },

    activeChartData () {
      return Array.isArray(this.chartData) ? this.chartData[this.chartDataCurrencyIndex] : { data: [] }
    },

    currency () {
      return this.$helper.currencySignByCode(this.activeChartData.currency)
    },

    activeCurrencyChart: {
      set (val) {
        this.$store.commit('transaction/setChartDataCurrencyIndex', val)
      },
      get () {
        return this.chartDataCurrencyIndex
      }
    }
  },

  watch: {
    activeChartData () {
      this.renderChart()
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chartdiv, am4charts.XYChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU

    chart.leftAxesContainer.layout = 'vertical'

    // Date axis for days
    this.dateAxis2 = chart.xAxes.push(new am4charts.DateAxis())
    this.dateAxis2.baseInterval = { timeUnit: 'day', count: 1 }
    this.dateAxis2.renderer.grid.template.location = 0
    this.dateAxis2.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06
    // this.dateAxis2.renderer.grid.template.disabled = true

    // Date axis for groups
    this.dateAxis = chart.xAxes.push(new am4charts.DateAxis())
    this.dateAxis.groupData = true
    this.dateAxis.groupCount = 360
    this.dateAxis.groupIntervals.setAll([
      { timeUnit: 'day', count: 1 },
      { timeUnit: 'month', count: 1 }
    ])
    this.dateAxis.cursorTooltipEnabled = false
    this.dateAxis.renderer.grid.template.location = 0
    this.dateAxis.renderer.grid.template.disabled = true
    this.dateAxis.renderer.labels.template.disabled = true
    // this.dateAxis.renderer.ticks.template.disabled = true
    // this.dateAxis.height = 0

    // Cols axis
    this.colsAxis = chart.yAxes.push(new am4charts.ValueAxis())
    this.colsAxis.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06

    // Legend
    chart.legend = new am4charts.Legend()

    // Cursor
    chart.cursor = new am4charts.XYCursor()
    chart.cursor.xAxis = this.dateAxis
    chart.cursor.events.on('zoomended', (ev) => {
      if (ev.target.behavior === 'none') return
      const range = ev.target.xRange
      const from = this.$moment(this.dateAxis2.positionToDate(this.dateAxis2.toAxisPosition(range.start))).format('YYYY-MM-DD')
      const to = this.$moment(this.dateAxis2.positionToDate(this.dateAxis2.toAxisPosition(range.end))).format('YYYY-MM-DD')
      this.setDetailsInterval({ from, to })
    })

    // Currend day line
    const dateBorder = this.dateAxis2.axisRanges.create()
    dateBorder.date = new Date()
    dateBorder.grid.stroke = prefersColorSchemeDark ? am4core.color('#FFF') : am4core.color('#333333')
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
    // const rangeFututre = this.dateAxis2.axisRanges.create()
    // rangeFututre.date = new Date()
    // rangeFututre.endDate = new Date(2100, 0, 3)
    // rangeFututre.grid.disabled = true
    // rangeFututre.axisFill.fillOpacity = 0.6
    // rangeFututre.axisFill.fill = '#FFFFFF'

    // Scrollbar on the bottom
    chart.scrollbarX = new am4core.Scrollbar()
    chart.scrollbarX.parent = chart.bottomAxesContainer
    // chart.scrollbarX.background.fill = am4core.color('#f3f3f3')
    // chart.scrollbarX.thumb.background.fill = am4core.color('#f3f3f3')
    // chart.scrollbarX.stroke = am4core.color('#f3f3f3')

    const dateAxisChanged = () => {
      const from = this.$moment(this.dateAxis2.minZoomed).format('YYYY-MM-DD')
      const to = this.$moment(this.dateAxis2.maxZoomed).format('YYYY-MM-DD')
      this.setDetailsInterval({ from, to })
    }

    chart.scrollbarX.thumb.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.startGrip.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.endGrip.events.on('dragstop', dateAxisChanged)
    chart.zoomOutButton.events.on('hit', () => {
      this.setDetailsInterval({ from: '', to: '' })
    })

    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/setDetailsInterval') {
        if (mutation.payload.from === '') {
          this.dateAxis2.zoom({ start: 0, end: 1 })
        }
      }
    })

    /**
   * ========================================================
   * Enabling responsive features
   * ========================================================
   */

    chart.responsive.enabled = true

    chart.responsive.rules.push({
      relevant: (target) => {
        if (target.pixelWidth <= 400) {
          if (target.cursor.behavior !== 'none') target.cursor.behavior = 'none'
          return true
        }
        if (target.cursor.behavior === 'none') target.cursor.behavior = 'zoomX'
        return false
      },
      state: (target, stateId) => {
        if (target instanceof am4charts.Chart) {
          const state = target.states.create(stateId)
          state.properties.paddingTop = 10
          state.properties.paddingRight = 0
          state.properties.paddingBottom = 0
          state.properties.paddingLeft = 0
          return state
        }

        if (target instanceof am4charts.ValueAxis) {
          const state = target.states.create(stateId)
          state.properties.cursorTooltipEnabled = false
          return state
        }

        if ((target instanceof am4charts.AxisLabel) && (target.parent instanceof am4charts.AxisRendererY)) {
          const state = target.states.create(stateId)
          state.properties.inside = true
          return state
        }
        return null
      }
    })

    this.chart = chart
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

      let filledChartData = []

      if (this.activeChartData.data.length) {
        const istart = this.$moment(this.$store.state.transaction.queryParams.from)
        const iend = this.$moment(this.$store.state.transaction.queryParams.to)
        const daysInInterval = iend.diff(istart, 'days') + 1

        // Filling empty data
        filledChartData = new Array(daysInInterval).fill(null).map((e, i) => {
          return {
            period: this.$moment(this.$store.state.transaction.queryParams.from).add(i, 'd').format('YYYY-MM-DD'),
            amountIncome: null,
            amountExpense: null,
            balance: null
          }
        })

        // Merge empty period with days with data
        this.activeChartData.data.forEach(element => {
          const i = filledChartData.findIndex(e => e.period === element.period)
          if (i > -1) {
            filledChartData.splice(i, 1, element)
          }
        })

        // Filling daily balance
        let previosValue = null
        filledChartData.map(e => {
          if (e.balance !== null) {
            previosValue = e.balance
          }
          if (e.balance === null) {
            e.balance = previosValue
          }
          return e
        })
      }

      // this.chart.events.on('datavalidated', () => {
      //   setTimeout(() => {
      //     this.dataValidated = true
      //   }, 0)
      // })

      this.chart.data = filledChartData
      this.chart.xAxes.values[0].min = (new Date(this.$store.state.transaction.queryParams.from)).getTime()
      this.chart.xAxes.values[0].max = (new Date(this.$store.state.transaction.queryParams.to)).getTime()

      if (this.showSeries('amountIncome')) this.addIncomeSeries()
      if (this.showSeries('amountExpense')) this.addExpenseSeries()
      if (this.showSeries('balance')) {
        this.colsAxis.height = am4core.percent(65)
        this.addBalanceSeries()
      } else {
        this.colsAxis.height = am4core.percent(100)
      }
    },

    addIncomeSeries () {
      this.incomeSeries = this.chart.series.push(new am4charts.ColumnSeries())
      this.incomeSeries.name = this.$t('income')
      this.incomeSeries.tooltip.background.filters.clear()
      this.incomeSeries.tooltip.background.strokeWidth = 0
      this.incomeSeries.tooltip.getFillFromObject = false
      this.incomeSeries.tooltip.background.fill = am4core.color('#3ec55e')
      this.incomeSeries.tooltip.label.fill = am4core.color('#333')
      this.incomeSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
      this.incomeSeries.yAxis = this.colsAxis
      this.incomeSeries.xAxis = this.dateAxis
      this.incomeSeries.dataFields.valueY = 'amountIncome'
      this.incomeSeries.dataFields.dateX = 'period'
      this.incomeSeries.groupFields.valueY = 'sum'
      this.incomeSeries.stroke = am4core.color('#3ec55e')
      this.incomeSeries.columns.template.stroke = am4core.color('#3ec55e')
      this.incomeSeries.columns.template.fill = am4core.color('#3ec55e')
      this.incomeSeries.columns.template.fillOpacity = 0.5
      this.incomeSeries.defaultState.transitionDuration = 0

      this.incomeSeries.adapter.add('tooltipText', (t, target) => {
        const isGrouped = !!target.tooltipDataItem.groupDataItems
        const dateFormat = isGrouped ? 'MMM yyyy' : 'd MMMM yyyy'
        return `{dateX.formatDate('${dateFormat}')}\n{name}: {valueY.value} ${this.currency}`
      })
    },

    addExpenseSeries () {
      this.expenseSeries = this.chart.series.push(new am4charts.ColumnSeries())
      this.expenseSeries.name = this.$t('expense')
      this.expenseSeries.tooltip.background.filters.clear()
      this.expenseSeries.tooltip.background.strokeWidth = 0
      this.expenseSeries.tooltip.getFillFromObject = false
      this.expenseSeries.tooltip.background.fill = am4core.color('#fc3d38')
      this.expenseSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
      this.expenseSeries.yAxis = this.colsAxis
      this.expenseSeries.xAxis = this.dateAxis
      this.expenseSeries.dataFields.valueY = 'amountExpense'
      this.expenseSeries.dataFields.dateX = 'period'
      this.expenseSeries.groupFields.valueY = 'sum'
      this.expenseSeries.stroke = am4core.color('#fc3d38')
      this.expenseSeries.columns.template.stroke = am4core.color('#fc3d38')
      this.expenseSeries.columns.template.fill = am4core.color('#fc3d38')
      this.expenseSeries.columns.template.fillOpacity = 0.5
      this.expenseSeries.defaultState.transitionDuration = 0

      this.expenseSeries.adapter.add('tooltipText', (t, target) => {
        const isGrouped = !!target.tooltipDataItem.groupDataItems
        const dateFormat = isGrouped ? 'MMM yyyy' : 'd MMMM yyyy'
        return `{dateX.formatDate('${dateFormat}')}\n{name}: {valueY.value} ${this.currency}`
      })
    },

    addBalanceSeries () {
      this.addBalanceAxis().events.on('ready', () => {
        this.balanceSeries = this.chart.series.push(new am4charts.LineSeries())
        this.balanceSeries.name = this.$t('balance')
        this.balanceSeries.tooltip.pointerOrientation = 'top'
        this.balanceSeries.tooltip.background.filters.clear()
        this.balanceSeries.tooltip.background.strokeWidth = 0
        this.balanceSeries.tooltip.getFillFromObject = false
        this.balanceSeries.tooltip.background.fill = am4core.color('#333')
        this.balanceSeries.tooltip.label.fill = am4core.color('#FFF')
        this.balanceSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
        this.balanceSeries.yAxis = this.balanceAxis
        this.balanceSeries.xAxis = this.dateAxis2
        this.balanceSeries.dataFields.valueY = 'balance'
        this.balanceSeries.dataFields.dateX = 'period'
        this.balanceSeries.groupFields.valueY = 'sum'
        this.balanceSeries.stroke = am4core.color('#3ec55e')
        this.balanceSeries.strokeWidth = 2
        // this.balanceSeries.strokeOpacity = 0.8
        this.balanceSeries.defaultState.transitionDuration = 0

        // Create a range to change stroke for values below 0
        const range = this.balanceAxis.createSeriesRange(this.balanceSeries)
        range.value = 0
        range.endValue = -100000000
        range.contents.stroke = am4core.color('#fc3d38')
        range.contents.fill = am4core.color('#fc3d38')
        range.contents.fillOpacity = 0.2

        this.balanceSeries.events.on('datavalidated', (ev) => {
          let dates = []
          let previous = null
          ev.target.data.forEach((e, i, arr) => {
            if (e.balance < 0) {
              dates.push({
                balance: e.balance,
                date: e.period,
                isStart: previous === null,
                isEnd: false
              })
              if (i === arr.length - 1) {
                if (dates.length) {
                  dates[dates.length - 1].isEnd = true
                  this.addNegativeBalanceRange(ev.target, dates)
                }
              }
              previous = e.balance
            } else {
              if (dates.length) {
                this.addNegativeBalanceRange(ev.target, dates)
              }
              dates = []
              previous = e.balance
            }
          })
        })
      })
    },

    addNegativeBalanceRange (target, dates) {
      const minimumAmount = dates.reduce((min, e) => {
        return e.balance < min ? e.balance : min
      }, dates[0].balance)
      const minimumDate = dates.find(d => d.balance === minimumAmount).date

      const startDate = dates[0].date
      const endDate = dates[dates.length - 1].date
      const itoday = this.$moment()
      const istart = this.$moment(startDate)
      const iend = this.$moment(endDate)
      const daysInIntervalStart = istart.diff(itoday, 'days')
      const daysInIntervalEnd = iend.diff(itoday, 'days')
      const inDaysStart = (daysInIntervalStart > 0) ? ` (in ${daysInIntervalStart} days)` : ''
      const inDaysEnd = (daysInIntervalEnd > 0 && !dates[dates.length - 1].isEnd) ? ` (in ${daysInIntervalEnd} days)` : ''

      const nbr = this.dateAxis2.createSeriesRange(target)
      nbr.date = new Date(dates[0].date)
      nbr.endDate = new Date(dates[dates.length - 1].date)
      nbr.contents.strokeWidth = 0
      nbr.contents.strokeOpacity = 0
      nbr.axisFill.interactionsEnabled = true
      nbr.axisFill.isMeasured = true
      nbr.axisFill.tooltip = new am4core.Tooltip()
      nbr.axisFill.tooltip.background.filters.clear()
      nbr.axisFill.tooltip.background.strokeWidth = 0
      nbr.axisFill.tooltip.getFillFromObject = false
      nbr.axisFill.tooltip.background.fill = am4core.color('#fc3d38')
      nbr.axisFill.tooltip.label.fill = am4core.color('#4a0900')
      nbr.axisFill.tooltipY = this.balanceAxis.renderer.baseGrid.y
      nbr.axisFill.tooltipText = `CASH GAP!\nStart date:${dates[0].isStart ? ' <=' : ''} ${startDate}${inDaysStart}\nEnd date:${dates[dates.length - 1].isEnd ? ' >=' : ''} ${endDate}${inDaysEnd}\nMax balance decline: ${this.$numeral(minimumAmount).format()} ${this.currency} on ${minimumDate}`

      // Remove range when balance series removed
      this.balanceSeries.events.on('beforedisposed', (ev) => {
        const i = ev.target.xAxis.axisRanges.indexOf(nbr)
        if (i > -1) ev.target.xAxis.axisRanges.removeIndex(i).dispose()
      })
    },

    addBalanceAxis () {
      this.balanceAxis = this.chart.yAxes.push(new am4charts.ValueAxis())
      this.balanceAxis.height = am4core.percent(35)
      this.balanceAxis.marginBottom = 30
      if (!prefersColorSchemeDark) {
        this.balanceAxis.renderer.gridContainer.background.fill = am4core.color('#f3f3f3')
        this.balanceAxis.renderer.gridContainer.background.fillOpacity = 0.3
      }
      this.balanceAxis.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06
      this.balanceAxis.insertBefore(this.colsAxis)

      return this.balanceAxis
    },

    removeSeries (seriesToRemove) {
      const i = this.chart.series.indexOf(seriesToRemove)
      if (i > -1) this.chart.series.removeIndex(i).dispose()
    },

    showSeries (series) {
      return this.activeChartData.data.length &&
        this.activeChartData.data[0][series] !== null
    }

  }

}
</script>

<style lang="scss">
  .chart-container {
    position: relative;
    margin-bottom: 1rem;

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

    .chart-main {
      width: 100%;
      height: 500px;

      @media (max-width: 768px) {
        height: 400px !important;
      }
    }

  }

</style>

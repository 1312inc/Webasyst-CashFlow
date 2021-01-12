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
    ...mapState('transaction', ['queryParams', 'chartData', 'chartDataCurrencyIndex', 'loadingChart']),

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

  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams' && !mutation.payload.silent) {
        this.getChartData()
      }
    })

    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action) => {
        if (
          (action.type === 'transaction/update' ||
            action.type === 'transaction/delete' ||
            action.type === 'transactionBulk/bulkDelete' ||
            action.type === 'transactionBulk/bulkMove' ||
            action.type === 'category/delete') && !action.payload.silent
        ) {
          this.getChartData()
        }
      }
    })
  },

  mounted () {
    const chart = am4core.create(this.$refs.chartdiv, am4charts.XYChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU

    chart.leftAxesContainer.layout = 'vertical'

    // Date axis for days (balance)
    const dateAxis2 = chart.xAxes.push(new am4charts.DateAxis())
    dateAxis2.baseInterval = { timeUnit: 'day', count: 1 }
    dateAxis2.renderer.grid.template.location = 0.5
    dateAxis2.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06
    dateAxis2.renderer.grid.template.disabled = true
    dateAxis2.renderer.labels.template.disabled = true
    dateAxis2.cursorTooltipEnabled = false
    this.dateAxis2 = dateAxis2

    // Date axis for groups (columns)
    const dateAxis = chart.xAxes.push(new am4charts.DateAxis())
    dateAxis.groupData = true
    dateAxis.groupCount = 360
    dateAxis.groupIntervals.setAll([
      { timeUnit: 'day', count: 1 },
      { timeUnit: 'month', count: 1 }
    ])
    dateAxis.renderer.grid.template.location = 0.5
    dateAxis.renderer.grid.template.disabled = true
    dateAxis.renderer.ticks.template.disabled = false
    dateAxis.renderer.ticks.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.2
    dateAxis.renderer.ticks.template.strokeWidth = 1
    dateAxis.renderer.ticks.template.length = 8
    dateAxis.renderer.ticks.template.location = 0.5
    dateAxis.renderer.labels.template.location = 0.5
    this.dateAxis = dateAxis

    // Balance Axis
    const balanceAxis = chart.yAxes.push(new am4charts.ValueAxis())
    balanceAxis.height = 100
    balanceAxis.marginBottom = 60
    balanceAxis.extraMin = 0.1
    balanceAxis.extraMax = 0.1
    balanceAxis.cursorTooltipEnabled = false
    balanceAxis.numberFormatter = new am4core.NumberFormatter()
    balanceAxis.numberFormatter.numberFormat = '# a'
    if (!prefersColorSchemeDark) {
      balanceAxis.renderer.gridContainer.background.fill = am4core.color('#f3f3f3')
      balanceAxis.renderer.gridContainer.background.fillOpacity = 0.3
    }
    balanceAxis.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06
    this.balanceAxis = balanceAxis

    // Cols axis
    const colsAxis = chart.yAxes.push(new am4charts.ValueAxis())
    colsAxis.renderer.grid.template.strokeOpacity = prefersColorSchemeDark ? 0.16 : 0.06
    // colsAxis.height = am4core.percent(60)
    colsAxis.cursorTooltipEnabled = false
    colsAxis.numberFormatter = new am4core.NumberFormatter()
    colsAxis.numberFormatter.numberFormat = '# a'
    this.colsAxis = colsAxis

    // Legend
    chart.legend = new am4charts.Legend()

    // Cursor
    const cursor = new am4charts.XYCursor()
    cursor.xAxis = this.dateAxis
    cursor.lineY.disabled = true
    cursor.events.on('zoomended', (ev) => {
      if (ev.target.behavior === 'none') return
      const range = ev.target.xRange
      const from = this.$moment(this.dateAxis2.positionToDate(this.dateAxis2.toAxisPosition(range.start))).format('YYYY-MM-DD')
      const to = this.$moment(this.dateAxis2.positionToDate(this.dateAxis2.toAxisPosition(range.end))).format('YYYY-MM-DD')
      this.setDetailsInterval({ from, to })
    })
    chart.cursor = cursor

    // Currend day line
    const dateBorder = this.dateAxis.axisRanges.create()
    dateBorder.date = this.$moment().set('hour', 12).toDate()
    dateBorder.grid.stroke = prefersColorSchemeDark ? am4core.color('#FFF') : am4core.color('#333333')
    dateBorder.grid.strokeWidth = 1
    dateBorder.grid.strokeOpacity = 0.3
    dateBorder.label.inside = true
    dateBorder.label.valign = 'middle'
    dateBorder.label.text = this.$t('today')
    dateBorder.label.fill = dateBorder.grid.stroke
    dateBorder.label.fillOpacity = 0.6
    dateBorder.label.rotation = -90
    dateBorder.label.verticalCenter = 'middle'
    dateBorder.label.dx = -8

    // Scrollbar on the bottom
    chart.scrollbarX = new am4core.Scrollbar()
    chart.scrollbarX.parent = chart.bottomAxesContainer

    const dateAxisChanged = () => {
      const f = this.dateAxis2.minZoomed || this.dateAxis.minZoomed
      const t = this.dateAxis2.maxZoomed || this.dateAxis.maxZoomed
      const from = this.$moment(f).format('YYYY-MM-DD')
      const to = this.$moment(t).format('YYYY-MM-DD')
      this.setDetailsInterval({ from, to })
    }

    chart.scrollbarX.thumb.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.startGrip.events.on('dragstop', dateAxisChanged)
    chart.scrollbarX.endGrip.events.on('dragstop', dateAxisChanged)
    chart.zoomOutButton.events.on('hit', () => {
      this.setDetailsInterval({ from: '', to: '' })
    })

    this.unsubscribeFromDetailsInterval = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/setDetailsInterval' && mutation.payload.initiator === 'DetailsDashboard') {
        if (!mutation.payload.from) {
          this.dateAxis2.zoom({ start: 0, end: 1 })
        } else {
          this.dateAxis.zoomToDates(new Date(mutation.payload.from), new Date(mutation.payload.to))
          this.dateAxis2.zoomToDates(new Date(mutation.payload.from), new Date(mutation.payload.to))
        }
      }
    })

    /**
   * ========================================================
   * Enabling responsive features
   * ========================================================
   */

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
          if (target.marginBottom === 60) {
            state.properties.marginBottom = 40
          }
          return state
        }

        if (target instanceof am4core.Scrollbar) {
          const state = target.states.create(stateId)
          state.properties.paddingLeft = 14
          state.properties.paddingRight = 14
          return state
        }

        if ((target instanceof am4charts.AxisLabel) && (target.parent instanceof am4charts.AxisRendererY)) {
          const state = target.states.create(stateId)
          state.properties.inside = true
          state.properties.dx = -8
          state.properties.dy = -8
          state.properties.fillOpacity = 0.6
          state.properties.fontSize = 9
          return state
        }
        return null
      }
    })

    this.chart = chart
  },

  beforeDestroy () {
    this.unsubscribeFromQueryParams()
    this.unsubscribeFromTransitionUpdate()
    this.unsubscribeFromDetailsInterval()
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    ...mapMutations('transaction', ['setDetailsInterval']),

    async getChartData () {
      try {
        await this.$store.dispatch('transaction/getChartData')
      } catch (e) {
        this.$notify.error(`Method: getChartData<br>${e}`)
      }
    },

    renderChart () {
      // Delete negative ranges
      this.dateAxis2.axisRanges.each((e, i) => {
        this.dateAxis2.axisRanges.removeIndex(i).dispose()
      })

      this.addIncomeSeries()
      this.addExpenseSeries()
      this.addBalanceSeries()

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
          if (i > -1) filledChartData.splice(i, 1, element)
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

      this.chart.data = filledChartData
      // this.chart.xAxes.values[0].min = (new Date(this.$store.state.transaction.queryParams.from)).getTime()
      // this.chart.xAxes.values[0].max = (new Date(this.$store.state.transaction.queryParams.to)).getTime()
    },

    addIncomeSeries () {
      const i = this.chart.series.indexOf(this.incomeSeries)
      if (i > -1) {
        this.chart.series.removeIndex(i).dispose()
        delete this.incomeSeries
      }

      if (!this.activeChartData.data.length ||
        this.activeChartData.data[0].amountIncome === null) {
        return false
      }

      const incomeSeries = this.chart.series.push(new am4charts.ColumnSeries())
      incomeSeries.name = this.$t('income')
      incomeSeries.tooltip.background.filters.clear()
      incomeSeries.tooltip.background.strokeWidth = 0
      incomeSeries.tooltip.getFillFromObject = false
      incomeSeries.tooltip.background.fill = am4core.color('#3ec55e')
      incomeSeries.tooltip.label.fill = am4core.color('#333')
      incomeSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
      incomeSeries.yAxis = this.colsAxis
      incomeSeries.xAxis = this.dateAxis
      incomeSeries.dataFields.valueY = 'amountIncome'
      incomeSeries.dataFields.dateX = 'period'
      incomeSeries.groupFields.valueY = 'sum'
      incomeSeries.stroke = am4core.color('#3ec55e')
      incomeSeries.columns.template.stroke = am4core.color('#3ec55e')
      incomeSeries.columns.template.fill = am4core.color('#3ec55e')
      incomeSeries.columns.template.fillOpacity = 0.5
      incomeSeries.defaultState.transitionDuration = 0

      incomeSeries.adapter.add('tooltipText', (t, target) => {
        const isGrouped = !!target.tooltipDataItem.groupDataItems
        const dateFormat = isGrouped ? 'MMM yyyy' : 'd MMMM yyyy'
        return `{dateX.formatDate('${dateFormat}')}\n{name}: {valueY.value} ${this.currency}`
      })

      this.incomeSeries = incomeSeries
    },

    addExpenseSeries () {
      const i = this.chart.series.indexOf(this.expenseSeries)
      if (i > -1) {
        this.chart.series.removeIndex(i).dispose()
        delete this.expenseSeries
      }

      if (!this.activeChartData.data.length ||
        this.activeChartData.data[0].amountExpense === null) {
        return false
      }

      const expenseSeries = this.chart.series.push(new am4charts.ColumnSeries())
      expenseSeries.name = this.$t('expense')
      expenseSeries.tooltip.background.filters.clear()
      expenseSeries.tooltip.background.strokeWidth = 0
      expenseSeries.tooltip.getFillFromObject = false
      expenseSeries.tooltip.background.fill = am4core.color('#fc3d38')
      expenseSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
      expenseSeries.yAxis = this.colsAxis
      expenseSeries.xAxis = this.dateAxis
      expenseSeries.dataFields.valueY = 'amountExpense'
      expenseSeries.dataFields.dateX = 'period'
      expenseSeries.groupFields.valueY = 'sum'
      expenseSeries.stroke = am4core.color('#fc3d38')
      expenseSeries.columns.template.stroke = am4core.color('#fc3d38')
      expenseSeries.columns.template.fill = am4core.color('#fc3d38')
      expenseSeries.columns.template.fillOpacity = 0.5
      expenseSeries.defaultState.transitionDuration = 0

      expenseSeries.adapter.add('tooltipText', (t, target) => {
        const isGrouped = !!target.tooltipDataItem.groupDataItems
        const dateFormat = isGrouped ? 'MMM yyyy' : 'd MMMM yyyy'
        return `{dateX.formatDate('${dateFormat}')}\n{name}: {valueY.value} ${this.currency}`
      })

      this.expenseSeries = expenseSeries
    },

    addBalanceSeries () {
      const i = this.chart.series.indexOf(this.balanceSeries)
      if (i > -1) {
        this.chart.series.removeIndex(i).dispose()
        delete this.balanceSeries
      }

      this.balanceAxis.disabled = true

      if (!this.activeChartData.data.length ||
        this.activeChartData.data[0].balance === null) {
        return false
      }

      this.balanceAxis.disabled = false

      const balanceSeries = this.chart.series.push(new am4charts.LineSeries())
      balanceSeries.name = this.$t('balance')
      balanceSeries.tooltip.pointerOrientation = 'top'
      balanceSeries.tooltip.background.filters.clear()
      balanceSeries.tooltip.background.strokeWidth = 0
      balanceSeries.tooltip.getFillFromObject = false
      balanceSeries.tooltip.background.fill = am4core.color('#333')
      balanceSeries.tooltip.label.fill = am4core.color('#FFF')
      balanceSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currency}`
      balanceSeries.yAxis = this.balanceAxis
      balanceSeries.xAxis = this.dateAxis2
      balanceSeries.dataFields.valueY = 'balance'
      balanceSeries.dataFields.dateX = 'period'
      balanceSeries.groupFields.valueY = 'sum'
      balanceSeries.stroke = am4core.color('rgba(255, 0, 0, 0)')
      balanceSeries.strokeWidth = 2
      balanceSeries.defaultState.transitionDuration = 0
      this.balanceSeries = balanceSeries

      // Create a range to change stroke for positive values
      const rangePositive = this.balanceAxis.createSeriesRange(this.balanceSeries)
      rangePositive.value = 0
      rangePositive.endValue = Number.MAX_SAFE_INTEGER
      rangePositive.contents.stroke = am4core.color('#3ec55e')

      // Create a range to change stroke for negative values
      const rangeNegative = this.balanceAxis.createSeriesRange(this.balanceSeries)
      rangeNegative.value = -1
      rangeNegative.endValue = Number.MIN_SAFE_INTEGER
      rangeNegative.contents.stroke = am4core.color('#fc3d38')
      rangeNegative.contents.fill = am4core.color('#fc3d38')
      rangeNegative.contents.fillOpacity = 0.2

      // Create a range to make stroke dashed in the future
      const rangeDashed = this.dateAxis.createSeriesRange(this.balanceSeries)
      rangeDashed.date = new Date()
      rangeDashed.endDate = new Date(8640000000000000)
      rangeDashed.contents.stroke = am4core.color('#f3f3f3')
      rangeDashed.contents.strokeDasharray = '4,8'
      rangeDashed.contents.strokeWidth = 3

      this.balanceSeries.events.on('datavalidated', (ev) => {
        const vals = ev.target.data.map(e => e.balance)
        const max = Math.max.apply(null, vals.map(Math.abs))
        this.balanceAxis.min = -max
        this.balanceAxis.max = max

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
      nbr.axisFill.tooltipY = 50
      nbr.axisFill.tooltipText = `CASH GAP!\nStart date:${dates[0].isStart ? ' <=' : ''} ${startDate}${inDaysStart}\nEnd date:${dates[dates.length - 1].isEnd ? ' >=' : ''} ${endDate}${inDaysEnd}\nMax balance decline: ${this.$numeral(minimumAmount).format()} ${this.currency} on ${minimumDate}`
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

<template>
  <div
    ref="chartdiv"
    :style="loadingChart && 'opacity:.3'"
    class="c-chart-main smaller"
  ></div>
</template>

<script>
import { locale } from '@/plugins/locale'
import { mapState, mapActions } from 'vuex'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themesDark from '@amcharts/amcharts4/themes/amchartsdark'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

let prefersColorSchemeDark = false

if (window.appState?.theme === 'dark' ||
  (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ||
  document.getElementById('wa-dark-mode')?.getAttribute('media') === '(prefers-color-scheme: light)') {
  prefersColorSchemeDark = true
  am4core.useTheme(am4themesDark)
}

export default {
  computed: {
    ...mapState('transaction', ['chartInterval', 'detailsInterval', 'chartData', 'chartDataCurrencyIndex', 'loadingChart']),
    activeChartData () {
      return this.chartData[this.chartDataCurrencyIndex]
    },
    currencySign () {
      return this.$helper.currencySignByCode(this.activeChartData?.currency)
    }
  },

  watch: {
    detailsInterval (val) {
      if (!val.from) {
        this.dateAxis.zoom({ start: 0, end: 1 })
        this.dateAxis2.zoom({ start: 0, end: 1 })
      } else {
        this.dateAxis.zoomToDates(new Date(val.from), new Date(val.to))
        this.dateAxis2.zoomToDates(new Date(val.from), new Date(val.to))
      }
    }
  },

  mounted () {
    this.createChart()
    this.addDarkModeObserver()
  },

  beforeDestroy () {
    if (this.darkModeObserver) {
      this.darkModeObserver.disconnect()
    }
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    ...mapActions('transaction', ['updateDetailsInterval']),

    createChart () {
      if (this.chart) {
        this.chart.dispose()
      }

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
      dateAxis.groupCount = process.env.VUE_APP_MODE === 'mobile' ? 90 : 180
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
      dateAxis.renderer.cellStartLocation = 0.15
      dateAxis.renderer.cellEndLocation = 0.85
      this.dateAxis = dateAxis
      this.dateAxis.events.on('groupperiodchanged', ({ target }) => {
        target.startLocation = target.currentDataSetId.includes('month') ? this.$moment(this.chartInterval.from).date() / this.$moment(this.chartInterval.from).daysInMonth() : 0
        target.endLocation = target.currentDataSetId.includes('month') ? this.$moment(this.chartInterval.to).date() / this.$moment(this.chartInterval.to).daysInMonth() : 1
      })

      // Balance Axis
      const balanceAxis = chart.yAxes.push(new am4charts.ValueAxis())
      balanceAxis.height = 100
      balanceAxis.marginBottom = 60
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
      colsAxis.min = 0
      this.colsAxis = colsAxis

      // Cursor
      const cursor = new am4charts.XYCursor()
      cursor.xAxis = this.dateAxis
      cursor.lineY.disabled = true
      cursor.events.on('zoomended', (ev) => {
        if (ev.target.behavior === 'none') return
        const range = ev.target.xRange
        const from = this.$moment(this.dateAxis.positionToDate(this.dateAxis.toAxisPosition(range.start))).format('YYYY-MM-DD')
        const to = this.$moment(this.dateAxis.positionToDate(this.dateAxis.toAxisPosition(range.end))).format('YYYY-MM-DD')
        this.updateDetailsInterval({ from, to })
      })
      chart.cursor = cursor

      // Future dates hover
      const rangeFututre = this.dateAxis.axisRanges.create()
      rangeFututre.date = new Date()
      rangeFututre.endDate = new Date(8640000000000000)
      rangeFututre.grid.disabled = true
      rangeFututre.axisFill.fillOpacity = 0.5
      rangeFututre.axisFill.fill = prefersColorSchemeDark ? '#000000' : '#FFFFFF'
      chart.seriesContainer.zIndex = -1

      // Currend day line
      const dateBorder = this.dateAxis.axisRanges.create()
      dateBorder.date = new Date()
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
        const from = this.$moment(this.dateAxis.minZoomed).format('YYYY-MM-DD')
        let to = this.$moment(this.dateAxis.maxZoomed)
        to = to > this.$moment(this.chartInterval.to) ? this.chartInterval.to : to.format('YYYY-MM-DD')
        this.updateDetailsInterval({ from, to })
      }

      chart.scrollbarX.thumb.events.on('dragstop', dateAxisChanged)
      chart.scrollbarX.startGrip.events.on('dragstop', dateAxisChanged)
      chart.scrollbarX.endGrip.events.on('dragstop', dateAxisChanged)
      chart.zoomOutButton.events.on('hit', () => {
        this.updateDetailsInterval({ from: '', to: '' })
      })

      /**
   * ========================================================
   * Enabling responsive features
   * ========================================================
   */

      chart.responsive.rules.push({
        relevant: (target) => {
          if (target.pixelWidth <= 768) {
            if (target.cursor.behavior !== 'none') target.cursor.behavior = 'none'
            return true
          }
          if (target.cursor.behavior === 'none') target.cursor.behavior = 'zoomX'
          return false
        },
        state: (target, stateId) => {
        // if (target instanceof am4charts.Chart) {
        //   const state = target.states.create(stateId)
        //   state.properties.paddingTop = 10
        //   state.properties.paddingRight = 0
        //   state.properties.paddingBottom = 0
        //   state.properties.paddingLeft = 0
        //   return state
        // }

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

      this.$watch(
        'activeChartData',
        (data) => {
          this.updateChartData(data)
        },
        {
          immediate: true
        }
      )
    },

    updateChartData (newChartData) {
      // Define interval in days
      const daysInInterval = this.$moment(this.chartInterval.to).diff(this.$moment(this.chartInterval.from), 'days') + 1

      // Filling empty data
      const filledChartData = new Array(daysInInterval).fill(null).map((e, i) => {
        return {
          period: this.$moment(this.chartInterval.from).add(i, 'd').format('YYYY-MM-DD'),
          amountIncome: null,
          amountExpense: null,
          balance: null
        }
      })

      if (newChartData) {
        this.balanceAxis.disabled = newChartData.data[0].balance === null
        this.chart.scrollbarX.disabled = false
        this.chart.cursor.disabled = false;
        ['amountIncome', 'amountExpense', 'amountProfit', 'balance'].forEach((dataField) => {
          const seriesIndex = this.chart.series.values.findIndex(s => s.dataFields.valueY === dataField)
          // if no data and series exists
          if (newChartData.data[0][dataField] === null && seriesIndex > -1) {
            this.chart.series.removeIndex(seriesIndex).dispose()
          }
          // if has data but series not exists
          if (newChartData.data[0][dataField] !== null && seriesIndex === -1) {
            if (dataField === 'balance') {
              this.addBalanceSeries()
            } else {
              this.addColumnSeries(dataField)
            }
          }
        })

        // Merge empty period with days with data
        newChartData.data.forEach(element => {
          const i = filledChartData.findIndex(e => e.period === element.period)
          if (i > -1) {
            filledChartData.splice(i, 1, {
       ***REMOVED***element,
              bulletDisabled: true
            })
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
          if (e.period === this.$moment().format('YYYY-MM-DD')) {
            e.bulletDisabled = false
          }
        })
      } else {
        this.balanceAxis.disabled = true
        this.chart.scrollbarX.disabled = true
        this.chart.cursor.disabled = true;
        ['amountIncome', 'amountExpense', 'amountProfit', 'balance'].forEach((dataField) => {
          const seriesIndex = this.chart.series.values.findIndex(s => s.dataFields.valueY === dataField)
          // remove series if exists
          if (seriesIndex > -1) {
            this.chart.series.removeIndex(seriesIndex).dispose()
          }
        })
        // Add dummy series
        this.addColumnSeries('amountIncome')
        // Fill dummy data
        filledChartData.forEach(element => {
          element.amountIncome = 0
        })
      }

      this.chart.data = filledChartData
    },

    addColumnSeries (dataField) {
      let options = {
        dataField
      }

      if (dataField === 'amountIncome') {
        options = {
          name: this.$t('income'),
          color: am4core.color('#3ec55e'),
   ***REMOVED***options
        }
      }

      if (dataField === 'amountExpense') {
        options = {
          name: this.$t('expense'),
          color: am4core.color('#fc3d38'),
   ***REMOVED***options
        }
      }

      if (dataField === 'amountProfit') {
        options = {
          name: this.$t('profit'),
          color: am4core.color('#1a9afe'),
   ***REMOVED***options
        }
      }

      const newSeries = this.chart.series.push(new am4charts.ColumnSeries())
      newSeries.name = options.name
      newSeries.tooltip.background.filters.clear()
      newSeries.tooltip.background.strokeWidth = 0
      newSeries.tooltip.getFillFromObject = false
      newSeries.tooltip.background.fill = options.color
      newSeries.tooltip.animationDuration = 500
      newSeries.yAxis = this.colsAxis
      newSeries.xAxis = this.dateAxis
      newSeries.dataFields.valueY = options.dataField
      newSeries.dataFields.dateX = 'period'
      newSeries.groupFields.valueY = 'sum'
      newSeries.stroke = options.color
      newSeries.columns.template.strokeWidth = 0
      newSeries.columns.template.fill = options.color
      newSeries.columns.template.fillOpacity = 0.5
      newSeries.columns.template.column.cornerRadiusTopLeft = 4
      newSeries.columns.template.column.cornerRadiusTopRight = 4

      newSeries.adapter.add('tooltipText', (t, target) => {
        const isGrouped = !!target.tooltipDataItem.groupDataItems
        const dateFormat = isGrouped ? 'MMM yyyy' : 'd MMMM yyyy'
        return `{dateX.formatDate('${dateFormat}')}\n{name}: {valueY.value} ${this.currencySign}`
      })
    },

    addBalanceSeries () {
      const balanceSeries = this.chart.series.push(new am4charts.LineSeries())
      balanceSeries.name = this.$t('balance')
      balanceSeries.tooltip.pointerOrientation = 'top'
      balanceSeries.tooltip.background.filters.clear()
      balanceSeries.tooltip.background.strokeWidth = 0
      balanceSeries.tooltip.getFillFromObject = false
      balanceSeries.tooltip.background.fill = am4core.color('#333')
      balanceSeries.tooltip.label.fill = am4core.color('#FFF')
      balanceSeries.tooltip.animationDuration = 500
      balanceSeries.tooltipText = `{dateX.formatDate('d MMMM yyyy')}\n{name}: {valueY.value} ${this.currencySign}`
      balanceSeries.yAxis = this.balanceAxis
      balanceSeries.xAxis = this.dateAxis2
      balanceSeries.dataFields.valueY = 'balance'
      balanceSeries.dataFields.dateX = 'period'
      balanceSeries.groupFields.valueY = 'sum'
      balanceSeries.stroke = am4core.color('rgba(255, 0, 0, 0)')
      balanceSeries.strokeWidth = 3

      const chartDataChangedEvent = this.chart.events.on('datavalidated', (ev) => {
        // Delete cash gap ranges
        this.dateAxis2.axisRanges.each((e, i) => {
          if (e.name === 'CashGap') {
            this.dateAxis2.axisRanges.removeIndex(i).dispose()
          }
        })

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
            if (i === arr.length - 1 && dates.length) {
              dates[dates.length - 1].isEnd = true
              this.addNegativeBalanceRange(balanceSeries, dates)
            }
          } else {
            if (dates.length) {
              this.addNegativeBalanceRange(balanceSeries, dates)
            }
            dates = []
          }
          previous = e.balance
        })
      })

      balanceSeries.events.on('beforedisposed', (ev) => {
        // Delete cash gap ranges
        this.dateAxis2.axisRanges.each((e, i) => {
          if (e.name === 'CashGap') {
            this.dateAxis2.axisRanges.removeIndex(i).dispose()
          }
        })
        chartDataChangedEvent.dispose()
      })

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
      const rangeDashed = this.dateAxis2.createSeriesRange(this.balanceSeries)
      rangeDashed.date = new Date()
      rangeDashed.endDate = new Date(8640000000000000)
      rangeDashed.contents.stroke = prefersColorSchemeDark ? '#000000' : am4core.color('#f3f3f3')
      rangeDashed.contents.strokeDasharray = '3 5'
      rangeDashed.contents.strokeWidth = 3

      // Add Today bullet
      const bullet = this.balanceSeries.bullets.push(new am4charts.CircleBullet())
      bullet.disabled = true
      bullet.propertyFields.disabled = 'bulletDisabled'
      bullet.events.on('inited', ({ target }) => {
        target.circle.fill = target.dataItem.valueY >= 0 ? am4core.color('#3ec55e') : am4core.color('#fc3d38')
      })
    },

    addNegativeBalanceRange (target, dates) {
      const minimumAmount = dates.reduce((min, e) => {
        return e.balance < min ? e.balance : min
      }, dates[0].balance)

      const minimumDate = dates.find(d => d.balance === minimumAmount).date
      const startDate = dates[0].date
      const endDate = dates[dates.length - 1].date
      const daysInIntervalStart = this.$moment(startDate).diff(this.$moment(), 'days') + 1
      const daysInIntervalEnd = this.$moment(endDate).diff(this.$moment(), 'days') + 1
      const inDaysStart = daysInIntervalStart > 0 ? ` ${this.$t('inDays', { days: daysInIntervalStart })}` : ''
      const inDaysEnd = (daysInIntervalEnd > 0 && !dates[dates.length - 1].isEnd) ? ` ${this.$t('inDays', { days: daysInIntervalEnd })}` : ''

      const nbr = this.dateAxis2.createSeriesRange(target)
      nbr.name = 'CashGap'
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
      nbr.axisFill.tooltip.animationDuration = 500
      nbr.axisFill.tooltipY = 50
      nbr.axisFill.tooltipText = this.$t('cashGapTooltip', {
        start: `${dates[0].isStart ? ' <=' : ''} ${this.$moment(startDate).format('L')}${inDaysStart}`,
        end: `${dates[dates.length - 1].isEnd ? ' >=' : ''} ${this.$moment(endDate).format('L')}${inDaysEnd}`,
        decline: this.$helper.toCurrency({ value: minimumAmount, currencyCode: this.activeChartData.currency }),
        declineDate: this.$moment(minimumDate).format('L')
      })
    },

    switchMode (scheme) {
      if (scheme === '(prefers-color-scheme: light)' || scheme === 'dark') {
        prefersColorSchemeDark = true
        am4core.useTheme(am4themesDark)
        this.createChart()
      } else {
        prefersColorSchemeDark = false
        am4core.unuseAllThemes()
        this.createChart()
      }
    },

    addDarkModeObserver () {
      const targetNode = document.getElementById('wa-dark-mode')
      if (targetNode) {
        const config = { attributes: true }
        const callback = (mutationsList, observer) => {
          for (const mutation of mutationsList) {
            if (mutation.attributeName === 'media') {
              this.switchMode(targetNode.getAttribute('media'))
            }
          }
        }
        this.darkModeObserver = new MutationObserver(callback)
        this.darkModeObserver.observe(targetNode, config)
      }

      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        const newColorScheme = e.matches ? 'dark' : 'light'
        this.switchMode(newColorScheme)
      })
    }

  }
}
</script>

<style lang="scss">
  .c-chart-main {
    height: 450px;

    @media (max-width: 768px) {
      height: 400px !important;
    }
  }
</style>

<template>
  <div class="c-chart-pie-sticky smaller" ref="chart"></div>
</template>

<script>
import { locale } from '@/plugins/locale'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {
  props: ['rawData', 'label'],

  computed: {
    featurePeriod () {
      return this.$store.state.transaction.featurePeriod
    },
    selectedTransactionsIds () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
    },
    futureLabelText () {
      return this.featurePeriod === 1
        ? this.$t('tomorrow')
        : this.$t('nextDays', { count: this.featurePeriod })
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU
    chart.innerRadius = am4core.percent(40)

    const pieLabel = chart.seriesContainer.createChild(am4core.Label)
    pieLabel.textAlign = 'middle'
    pieLabel.horizontalCenter = 'middle'
    pieLabel.verticalCenter = 'middle'
    this.pieLabel = pieLabel

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category'
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    pieSeries.slices.template.stroke = am4core.color('#fff')
    pieSeries.slices.template.strokeOpacity = 1
    pieSeries.interpolationDuration = 500

    // Add Chart data
    chart.data = this.$store.state.category.categories.map(c => {
      return {
        id: c.id,
        amount: 0,
        category: c.name,
        category_color: c.color
      }
    })
    // Push empty data item for the placeholer
    chart.data.push({
      id: null,
      amount: 0,
      category: 'empty',
      category_color: '#EEEEEE'
    })

    this.chart = chart

    this.renderChart(this.rawData)
    this.$watch('rawData', val => {
      this.renderChart(val)
    })
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    renderChart (rawData) {
      // if empty data
      if (!rawData.length) {
        this.chart.series.getIndex(0).slices.template.tooltipText = this.$t(
          'emptyList'
        )
        this.pieLabel.fontSize = 16
        this.pieLabel.text = this.futureLabelText
        this.chart.data.forEach(e => {
          e.amount = e.category === 'empty' ? 100 : 0
        })
        this.chart.invalidateRawData()
        return
      }

      // pie label formatting
      this.chart.series.getIndex(0).slices.template.tooltipText =
        "{category}: {value.formatNumber('#,###.##')}"

      // make label inside Chart
      if (this.selectedTransactionsIds.length) {
        this.pieLabel.text = this.selectedTransactionsIds.length
        this.pieLabel.fontSize = 36
      } else {
        if (this.label === 'future') {
          this.pieLabel.text = this.futureLabelText
        } else {
          this.pieLabel.text = this.$moment(this.label).isValid()
            ? this.$moment(this.label).format('MMMM YYYY')
            : this.$t(this.label)
        }
        this.pieLabel.fontSize = 16
      }

      // update data
      this.chart.data.forEach(e => {
        const index = rawData.findIndex(el => el.id === e.id)
        e.amount = index > -1 ? rawData[index].amount : 0
      })
      this.chart.invalidateRawData()
    }
  }
}
</script>

<style>
.c-chart-pie-sticky {
  height: 400px;
}
</style>

<template>
  <div class="c-chart-pie-sticky" ref="chart"></div>
</template>

<script>
import { locale } from '@/plugins/locale'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {
  props: [
    'rawData',
    'label',
    'isCounterMode',
    'totalTransactions',
    'currencyCode'
  ],

  computed: {
    featurePeriod () {
      return this.$store.state.transaction.featurePeriod
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
    chart.innerRadius = am4core.percent(90)

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
      category_color: 'rgba(128, 128, 128, 0.2)'
    })

    this.chart = chart

    this.$watch(
      '$props',
      () => {
        this.renderChart()
      },
      {
        deep: true,
        immediate: true
      }
    )
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    renderChart () {
      // make label inside Chart
      this.pieLabel.html = '<div class="black">'
      if (this.isCounterMode) {
        this.pieLabel.html += `<div class="large">${this.$t('selected', { count: this.label })}</div>`
      } else {
        this.pieLabel.html += `<div class="larger custom-mb-4">${this.$helper.currencySignByCode(
          this.currencyCode
        )}</div>`
        if (this.label === 'future') {
          this.pieLabel.html += `<div>${this.futureLabelText}</div>`
        } else {
          this.pieLabel.html += this.$moment(new Date(this.label)).isValid()
            ? `<div style="text-transform:capitalize;">${this.$moment(this.label).format('MMMM YYYY')}</div>`
            : `<div>${this.$t(this.label)}</div>`
        }
        this.pieLabel.html += `<div class="hint custom-mt-8">${this.$t('transactionsListCount', {
          count: this.totalTransactions
        })}</div>`
      }
      this.pieLabel.html += '</div>'

      // if empty data
      if (!this.rawData.length) {
        this.chart.series.getIndex(0).slices.template.tooltipText = this.$t(
          'emptyList'
        )
        this.chart.data.forEach(e => {
          e.amount = e.category === 'empty' ? 100 : 0
        })
        this.chart.invalidateRawData()
        return
      }

      // pie label formatting
      this.chart.series.getIndex(0).slices.template.tooltipText =
        "{category}: {value.formatNumber('#,###.##')}"

      // update data
      this.chart.data.forEach(e => {
        const index = this.rawData.findIndex(el => el.id === e.id)
        e.amount = index > -1 ? this.rawData[index].amount : 0
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

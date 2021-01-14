<template>
  <div>
    <div class="chart smaller custom-mb-24" ref="chart"></div>
    <TransactionControls v-if="selectedIds.length" direction="column" :notStick="true" />
  </div>
</template>

<script>
import TransactionControls from '@/components/TransactionControls'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
// import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {
  components: {
    TransactionControls
  },

  data () {
    return {
      selectedIds: [],
      activeGroupTransactions: []
    }
  },

  watch: {
    '$store.state.transactionBulk.selectedTransactionsIds' (val) {
      this.selectedIds = val
      this.renderChart()
    },

    '$store.state.transaction.activeGroupTransactions' (val) {
      this.activeGroupTransactions = val
      if (!this.selectedIds.length) {
        this.renderChart()
      }
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    // chart.language.locale = am4langRU
    chart.innerRadius = am4core.percent(40)

    const label = chart.seriesContainer.createChild(am4core.Label)
    label.textAlign = 'middle'
    label.horizontalCenter = 'middle'
    label.verticalCenter = 'middle'
    this.label = label

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries())
    pieSeries.dataFields.value = 'amount'
    pieSeries.dataFields.category = 'category'
    pieSeries.labels.template.disabled = true
    pieSeries.slices.template.propertyFields.fill = 'category_color'
    pieSeries.slices.template.stroke = am4core.color('#fff')
    pieSeries.slices.template.strokeOpacity = 1
    pieSeries.legendSettings.itemValueText = '{value}'

    // Add Legend
    chart.legend = new am4charts.Legend()
    chart.legend.maxHeight = 100
    chart.legend.scrollable = true
    chart.legend.valueLabels.template.align = 'right'
    chart.legend.valueLabels.template.textAlign = 'end'

    this.chart = chart
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  },

  methods: {
    renderChart () {
      const ids = this.selectedIds.length
        ? this.selectedIds
        : this.activeGroupTransactions

      if (typeof ids[0] === 'number') {
        this.label.text = ids.length
        this.label.fontSize = 36
      } else {
        this.label.text =
          this.$moment().diff(
            this.$moment(this.activeGroupTransactions[0].date),
            'days'
          ) === 0
            ? this.$t('today')
            : `${this.$moment(this.activeGroupTransactions[0].date).format(
                'MMMM'
              )}\n${this.$moment(this.activeGroupTransactions[0].date).format(
                'YYYY'
              )}`
        this.label.fontSize = 16
      }

      const res = ids.reduce((acc, id) => {
        const el =
          this.$store.getters['transaction/getTransactionById'](id) || id
        const category = this.$store.getters['category/getById'](el.category_id)
        if (!acc[category.name]) {
          acc[category.name] = {
            amount: el.amount,
            category: category.name,
            category_color: category.color
          }
        } else {
          acc[category.name].amount += el.amount
        }
        return acc
      }, {})

      this.chart.data = Object.values(res)
    }
  }
}
</script>

<style>
.chart {
  height: 400px;
}
</style>

<template>
  <div>
    <div class="chart smaller" ref="chart"></div>
    <transition name="fade">
      <!-- TODO: Exclude sticky from TransactionControls Component -->
      <!-- TODO: Remove 'transactionBulk/empty' from destroy() to routerTransitionMixin -->
      <TransactionControls
        v-if="selectedTransactionsIds.length"
        direction="column"
        :notStick="true"
      />
    </transition>
  </div>
</template>

<script>
import { locale } from '@/plugins/locale'
import { mapState } from 'vuex'
import TransactionControls from '@/components/TransactionControls'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4langRU from '@amcharts/amcharts4/lang/ru_RU'

export default {
  components: {
    TransactionControls
  },

  computed: {
    ...mapState('transaction', [
      'activeGroupTransactions',
      'defaultGroupTransactions'
    ]),
    ...mapState('transactionBulk', ['selectedTransactionsIds']),

    featurePeriod () {
      return this.$store.state.transaction.featurePeriod
    }
  },

  mounted () {
    const chart = am4core.create(this.$refs.chart, am4charts.PieChart)
    if (locale === 'ru_RU') chart.language.locale = am4langRU
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
    pieSeries.interpolationDuration = 500

    // Add Chart data
    chart.data = this.$store.state.category.categories.map(c => {
      return {
        amount: 0,
        category: c.name,
        category_color: c.color
      }
    })

    this.chart = chart

    this.renderChart()

    this.$watch(
      vm =>
        [
          vm.selectedTransactionsIds,
          vm.activeGroupTransactions,
          vm.defaultGroupTransactions
        ].join(),
      val => {
        this.renderChart()
      },
      {
        deep: true
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
      const ids = this.selectedTransactionsIds.length
        ? this.selectedTransactionsIds
        : this.activeGroupTransactions.length
          ? this.activeGroupTransactions
          : this.defaultGroupTransactions

      if (typeof ids[0] === 'number') {
        this.label.text = ids.length
        this.label.fontSize = 36
      } else {
        const diff = this.$moment().diff(this.$moment(ids[0].date), 'days')

        if (diff < 0) {
          this.label.text =
            this.featurePeriod === 1
              ? this.$t('tomorrow')
              : this.$t('nextDays', { count: this.featurePeriod })
        } else if (diff === 0) {
          this.label.text = this.$t('today')
        } else {
          this.label.text = `${this.$moment(ids[0].date).format(
            'MMMM'
          )}\n${this.$moment(ids[0].date).format('YYYY')}`
        }
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

      this.chart.data.forEach(e => {
        e.amount = res[e.category] ? res[e.category].amount : 0
      })

      this.chart.invalidateRawData()
    }
  }
}
</script>

<style>
.chart {
  height: 400px;
}
</style>

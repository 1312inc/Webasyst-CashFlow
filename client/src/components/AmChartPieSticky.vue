<template>
  <div>
    <div class="c-chart-pie-sticky smaller" ref="chart"></div>
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

  data () {
    return {
      chartTransactions: []
    }
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
      amount: 0,
      category: 'empty',
      category_color: '#eee'
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

  watch: {
    featurePeriod (val) {
      if (!this.chartTransactions.length) {
        this.makeFutureLabelText()
      }
    }
  },

  methods: {
    renderChart () {
      this.chartTransactions = this.selectedTransactionsIds.length
        ? this.selectedTransactionsIds
        : this.activeGroupTransactions.length
          ? this.activeGroupTransactions
          : this.defaultGroupTransactions

      this.chart.series.getIndex(0).slices.template.tooltipText = "{category}: {value.formatNumber('#,###.##')}"

      if (!this.chartTransactions.length) {
        this.chart.series.getIndex(0).slices.template.tooltipText = this.$t('emptyList')

        this.chart.data.forEach(e => {
          e.amount = e.category === 'empty' ? 100 : 0
        })
        this.chart.invalidateRawData()

        this.label.fontSize = 16
        this.makeFutureLabelText()
        return
      }

      if (typeof this.chartTransactions[0] === 'number') {
        this.label.text = this.chartTransactions.length
        this.label.fontSize = 36
      } else {
        const diff = this.$moment().diff(this.$moment(this.chartTransactions[0].date), 'days')

        if (diff < 0) {
          this.makeFutureLabelText()
        } else if (diff === 0) {
          this.label.text = this.$t('today')
        } else {
          this.label.text = `${this.$moment(this.chartTransactions[0].date).format(
            'MMMM'
          )}\n${this.$moment(this.chartTransactions[0].date).format('YYYY')}`
        }
        this.label.fontSize = 16
      }

      const res = this.chartTransactions.reduce((acc, id) => {
        const el =
          this.$store.getters['transaction/getTransactionById'](id) || id
        const category = this.$store.getters['category/getById'](el.category_id)
        if (!acc[category.id]) {
          acc[category.id] = {
            amount: el.amount,
            category: category.name,
            category_color: category.color
          }
        } else {
          acc[category.id].amount += el.amount
        }
        return acc
      }, {})

      this.chart.data.forEach(e => {
        e.amount = res[e.id] ? res[e.id].amount : 0
      })

      this.chart.invalidateRawData()
    },

    makeFutureLabelText () {
      this.label.text =
            this.featurePeriod === 1
              ? this.$t('tomorrow')
              : this.$t('nextDays', { count: this.featurePeriod })
    }
  }
}
</script>

<style>
.c-chart-pie-sticky {
  height: 400px;
}
</style>

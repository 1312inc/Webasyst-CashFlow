<template>
  <div>
    <AmChartPie2 :rawData="chartData" :type="chartType" />
    <AmChartLegend :legendItems="chartData" class="custom-mx-20" />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import AmChartPie2 from '@/components/AmChartPie2'
import AmChartLegend from '@/components/AmChartLegend'

export default {
  components: {
    AmChartPie2,
    AmChartLegend
  },

  data () {
    return {
      chartData: [],
      chartType: ''
    }
  },

  computed: {
    ...mapState('transaction', [
      'activeGroupTransactions',
      'defaultGroupTransactions'
    ]),
    ...mapState('transactionBulk', ['selectedTransactionsIds'])
  },

  created () {
    this.makeChartData()
    this.$watch(
      vm =>
        [
          vm.selectedTransactionsIds,
          vm.activeGroupTransactions,
          vm.defaultGroupTransactions
        ].join(),
      () => {
        this.makeChartData()
      },
      {
        deep: true
      }
    )
  },

  methods: {
    makeChartData () {
      const data = this.selectedTransactionsIds.length
        ? this.selectedTransactionsIds
        : this.activeGroupTransactions.length
          ? this.activeGroupTransactions
          : this.defaultGroupTransactions

      // if array of selectedTransactionsIds
      this.chartType = typeof data[0] === 'number' ? 'counter' : 'normal'

      this.chartData = Object.values(
        data.reduce((acc, el) => {
          // el can be an Object or an ID
          const transaction =
            this.$store.getters['transaction/getTransactionById'](el) || el
          const category = this.$store.getters['category/getById'](
            transaction.category_id
          )
          if (!acc[category.id]) {
            acc[category.id] = {
              id: category.id,
              date: transaction.date,
              amount: transaction.amount,
              category: category.name,
              category_color: category.color
            }
          } else {
            acc[category.id].amount += transaction.amount
          }
          return acc
        }, {})
      )
    }
  }
}
</script>

<template>
  <div>
    <AmChartPie2 :rawData="rawData" :label="label" />
    <AmChartLegend :legendItems="rawData" :currencyCode="currencyCode" class="custom-mx-20" />
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
      rawData: [],
      label: ''
    }
  },

  computed: {
    ...mapState('transaction', [
      'transactions',
      'activeGroupTransactions',
      'defaultGroupTransactions',
      'chartData',
      'chartDataCurrencyIndex'
    ]),
    ...mapState('transactionBulk', ['selectedTransactionsIds']),
    currencyCode () {
      if (this.chartData.length) {
        return this.chartData[this.chartDataCurrencyIndex].currency
      } else {
        const account = this.$store.getters['account/getById'](
          this.transactions.data[0].account_id
        )
        console.log(account.currency)
        return account.currency
      }
    }
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
        ? { items: this.selectedTransactionsIds }
        : this.activeGroupTransactions.items?.length
          ? this.activeGroupTransactions
          : this.defaultGroupTransactions

      this.label = data.name
      this.rawData = Object.values(
        data.items.reduce((acc, el) => {
          // el can be an Object or an ID
          const transaction =
            this.$store.getters['transaction/getTransactionById'](el) || el
          const category = this.$store.getters['category/getById'](
            transaction.category_id
          )
          const account = this.$store.getters['account/getById'](
            transaction.account_id
          )
          if (account.currency === this.currencyCode) {
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
          }
          return acc
        }, {})
      )
    }
  }
}
</script>

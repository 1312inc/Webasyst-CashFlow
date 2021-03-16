<template>
  <div>
    <div style="position: relative">
      <AmChartPie
        :rawData="isCounterMode ? rawData : rawDataByCurrency"
        :isCounterMode="isCounterMode"
        :totalTransactions="totalTransactions"
        :label="
          isCounterMode
            ? selectedTransactionsIds.length
            : activeGroupTransactions.name
        "
        :currencyCode="currencyCode"
      />

      <AmChartBulkControls
        v-if="$store.state.transactionBulk.selectedTransactionsIds.length"
        class="c-chart-bulk-controls"
      />
    </div>

    <AmChartLegend
      :legendItems="rawDataByCurrency"
      :currencyCode="currencyCode"
      class="custom-mx-20"
    />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import AmChartPie from './AmChartPie'
import AmChartLegend from './AmChartLegend'
import AmChartBulkControls from './AmChartBulkControls'
export default {
  components: {
    AmChartPie,
    AmChartLegend,
    AmChartBulkControls
  },

  computed: {
    ...mapState('transaction', [
      'transactions',
      'activeGroupTransactions',
      'chartData',
      'chartDataCurrencyIndex'
    ]),
    ...mapState('transactionBulk', ['selectedTransactionsIds']),
    isCounterMode () {
      return !!this.selectedTransactionsIds.length
    },
    currencyCode () {
      if (this.chartData.length) {
        return this.chartData[this.chartDataCurrencyIndex].currency
      } else {
        const account = this.$store.getters['account/getById'](
          this.transactions.data[0].account_id
        )
        return account.currency
      }
    },
    totalTransactions () {
      return this.activeGroupTransactions.items.filter(t => {
        const account = this.$store.getters['account/getById'](t.account_id)
        return account.currency === this.currencyCode
      }).length
    },
    rawDataByCurrency () {
      return this.rawData.filter(e => e.currency === this.currencyCode)
    },
    rawData () {
      const data = this.isCounterMode
        ? { items: this.selectedTransactionsIds }
        : this.activeGroupTransactions

      return Object.values(
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
          if (!acc[category.id]) {
            acc[category.id] = {
              id: category.id,
              date: transaction.date,
              amount: transaction.amount,
              currency: account.currency,
              category_name: category.name,
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

<style>
.c-chart-bulk-controls {
  position: absolute;
  top: 57%;
  left: 50%;
  transform: translateX(-50%);
}
</style>

<template>
  <AmChart />
</template>

<script>
import AmChart from './AmChart'
export default {
  components: {
    AmChart
  },

  created () {
    this.unsubscribeFromMutations = this.$store.subscribe(({ type }) => {
      switch (type) {
        case 'transaction/updateChartInterval':
        case 'transaction/updateTransactions':
        case 'transaction/deleteTransaction':
        case 'transaction/createTransactions':
          this.$store.dispatch('transaction/getChartData')
      }
    })

    this.unsubscribeFromActions = this.$store.subscribeAction({
      after: ({ type }) => {
        switch (type) {
          case 'updateCurrentEntity':
          case 'transactionBulk/bulkMove':
            this.$store.dispatch('transaction/getChartData')
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromMutations()
    this.unsubscribeFromActions()
  }
}
</script>

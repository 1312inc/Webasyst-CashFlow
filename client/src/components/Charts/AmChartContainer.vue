<template>
  <div>
    <AmChart v-if="$store.state.transaction.chartData.length" />
    <div v-else style="width: 100%; height: 450px"></div>
  </div>
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
        case 'transaction/setCreatedTransactions':
          this.$store.dispatch('transaction/getChartData')
      }
    })

    this.unsubscribeFromActions = this.$store.subscribeAction({
      after: ({ type }) => {
        switch (type) {
          case 'updateCurrentEntity':
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

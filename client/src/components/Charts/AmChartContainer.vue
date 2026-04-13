<template>
  <BlankBox>
    <AmChart />
  </BlankBox>
</template>

<script>
import AmChart from './AmChart'
import BlankBox from '../BlankBox.vue'

export default {
  components: {
    AmChart,
    BlankBox
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

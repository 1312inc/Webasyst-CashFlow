export default {
  beforeRouteUpdate (to, from, next) {
    this.clearDefaultGroupTransactions()
    next()
  },

  beforeRouteLeave (to, from, next) {
    this.clearDefaultGroupTransactions()
    next()
  },

  methods: {
    clearDefaultGroupTransactions () {
      this.$store.commit('transaction/setTransactions', [])
      this.$store.commit('transaction/setActiveGroupTransactions', [])
      this.$store.commit('transaction/setDefaultGroupTransactions', [])
      this.$store.commit('transactionBulk/emptySelectedTransactionsIds')
    }
  }
}

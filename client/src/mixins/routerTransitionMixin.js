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
      this.$store.commit('transaction/setTransactions', { data: [] })
      this.$store.commit('transactionBulk/emptySelectedTransactionsIds')
      this.$store.commit('transaction/setDetailsInterval', {
        from: '',
        to: ''
      })
    }
  }
}

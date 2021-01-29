export default {
  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if ((mutation.type === 'transaction/updateQueryParams' || mutation.type === 'transaction/setDetailsInterval') && !mutation.payload.silent) {
        this.getTransactions({ offset: 0 })
      }
    })

    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action) => {
        if (
          (action.type === 'transactionBulk/bulkDelete' ||
            action.type === 'transactionBulk/bulkMove' ||
            action.type === 'category/delete') && !action.payload.silent
        ) {
          this.getTransactions({ offset: 0 })
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromQueryParams()
    this.unsubscribeFromTransitionUpdate()
  }

}

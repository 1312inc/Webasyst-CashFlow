export default {
  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams' && !mutation.payload.silent) {
        this.getTransactions()
      }
    })

    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action) => {
        if (
          (action.type === 'transaction/update' ||
            action.type === 'transaction/delete' ||
            action.type === 'transactionBulk/bulkDelete' ||
            action.type === 'transactionBulk/bulkMove' ||
            action.type === 'category/delete') && !action.payload.silent
        ) {
          this.getTransactions()
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromQueryParams()
    this.unsubscribeFromTransitionUpdate()
  }

}

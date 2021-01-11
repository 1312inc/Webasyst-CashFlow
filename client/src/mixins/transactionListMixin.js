export default {
  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams' && !mutation.payload.silent) {
        this.getTransactions({ offset: 0 })
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

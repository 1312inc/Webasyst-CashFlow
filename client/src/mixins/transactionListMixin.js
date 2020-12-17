export default {
  computed: {
    showCheckbox () {
      return window.eventBus ? window.eventBus.multiSelect : true
    }
  },

  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams' && !mutation.payload.silent) {
        this.getTransactions()
      }
    })

    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action) => {
        if (
          action.type === 'transaction/update' ||
          action.type === 'transaction/delete' ||
          action.type === 'transactionBulk/bulkDelete' ||
          action.type === 'transactionBulk/bulkMove' ||
          action.type === 'account/delete' ||
          action.type === 'category/delete'
        ) {
          this.getTransactions()
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromQueryParams()
    this.unsubscribeFromTransitionUpdate()
  },

  methods: {
    checkAll (items) {
      const ids = items.map(e => e.id)
      const method = this.isCheckedAllInGroup(items) ? 'unselect' : 'select'
      this.$store.commit(`transactionBulk/${method}`, ids)
    },

    isCheckedAllInGroup (items) {
      return items.every(e => this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id))
    }
  }
}

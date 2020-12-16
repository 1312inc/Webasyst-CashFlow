export default {
  computed: {
    checkboxChecked: {
      get () {
        return this.checkedRows.length === this.filteredTransactions.length
      },
      set () {
        return false
      }
    },

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
          action.type === 'transaction/bulkDelete' ||
          action.type === 'transaction/bulkMove'
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
    checkAll ({ target }) {
      this.checkedRows = target.checked
        ? this.filteredTransactions.map(r => r.id)
        : []
      this.$emit('checkRows', this.checkedRows)
    },

    unCheckAll () {
      this.checkedRows = []
      this.$emit('checkRows', this.checkedRows)
    },

    onTransactionListRowUpdate (id) {
      const index = this.checkedRows.indexOf(id)
      if (index > -1) {
        this.checkedRows.splice(index, 1)
      } else {
        this.checkedRows.push(id)
      }
      this.$emit('checkRows', this.checkedRows)
    }
  }
}

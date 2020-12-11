export default {
  computed: {
    checkboxChecked: {
      get () {
        return this.checkedRows.length === this.filteredTransactions.length
      },
      set () {
        return false
      }
    }
  },

  created () {
    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action, state) => {
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

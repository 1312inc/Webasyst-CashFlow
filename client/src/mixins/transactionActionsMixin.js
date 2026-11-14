import { confirmDeletion } from '@/services/confirmDeletion'

export default {
  computed: {
    $_transactionActionsMixin_removalPending () {
      return this.$store.state.transactionBulk.removalPending
    }
  },

  methods: {
    $_transactionActionsMixin_bulkDelete () {
      return this.$_transactionActionsMixin_confirmBulkRemoval('bulkDelete', 'bulkDeleteWarning')
    },

    async $_transactionActionsMixin_confirmBulkRemoval (action, warning, label) {
      const selection = this.$store.state.transactionBulk
      if (selection.removalPending || selection.restorePending || !selection.selectedTransactionsIds.length) return
      const ids = [...selection.selectedTransactionsIds]
      this.$store.commit('transactionBulk/setRemovalPending', true)
      try {
        if (!await confirmDeletion(warning, label) || this._isDestroyed ||
            ids.length !== selection.selectedTransactionsIds.length ||
            !ids.every(id => selection.selectedTransactionsIds.includes(id))) return
        await this.$store.dispatch(`transactionBulk/${action}`, ids)
      } finally {
        this.$store.commit('transactionBulk/setRemovalPending', false)
      }
    },

    $_transactionActionsMixin_emptySelected () {
      this.$store.commit('transactionBulk/emptySelectedTransactionsIds')
    }
  }
}

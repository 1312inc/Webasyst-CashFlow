export default {
  methods: {
    $_transactionActionsMixin_bulkDelete () {
      if (confirm(this.$t('bulkDeleteWarning'))) {
        this.$store.dispatch('transactionBulk/bulkDelete')
      }
    },

    $_transactionActionsMixin_emptySelected () {
      this.$store.commit('transactionBulk/emptySelectedTransactionsIds')
    }
  }
}

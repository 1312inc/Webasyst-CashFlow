export default {
  computed: {
    $_amountMixin_amountTypes () {
      const currentEntity = this.$store.getters.getCurrentType
      return currentEntity?.is_profit
        ? ['profit']
        : currentEntity?.type
          ? [currentEntity.type]
          : ['income', 'expense', 'profit']
    }
  }
}

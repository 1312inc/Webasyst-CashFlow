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
  },

  methods: {
    $_amountMixin_amountDelta (period) {
      return (
        Math.abs(this.$_amountMixin_getTotalByType('income', period)) -
        Math.abs(this.$_amountMixin_getTotalByType('expense', period)) -
        Math.abs(this.$_amountMixin_getTotalByType('profit', period))
      )
    },

    $_amountMixin_getTotalByType (type, period) {
      const data = this.$store.state.transaction.chartData[
        this.$store.state.transaction.chartDataCurrencyIndex
      ]

      if (!data) return 0

      const itoday = this.$helper.currentDate
      const eltype = `amount${type[0].toUpperCase()}${type.slice(1)}`
      return data.data
        .filter(e => (period === 'to' ? e.period > itoday : e.period <= itoday))
        .reduce((acc, e) => acc + e[eltype], 0)
    }
  }
}

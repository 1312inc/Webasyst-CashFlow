<template>
  <div
    v-if="!loadingChart && message"
    :class="balanceChangeSignPoint.balance >= 0 ? 'text-green' : 'text-red'"
    class="custom-mt-8 custom-px-16-mobile bold"
  >
    <i
      :class="
        balanceChangeSignPoint.balance >= 0
          ? 'fa-arrow-circle-up'
          : 'fa-exclamation-triangle'
      "
      class="fas custom-mr-4"
    ></i>
    {{ message }}
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  computed: {
    ...mapState('transaction', ['loadingChart']),
    chartData () {
      return this.$store.getters['transaction/getChartDataByCurrentCurrency']
    },
    balanceChangeSignPoint () {
      if (!this.chartData || this.chartData.data[0]?.balance === null) {
        // if no data or data has no balance
        return false
      }

      const currentBalance = this.chartData.data
        .filter(e => e.period <= this.$helper.currentDate)
        .slice(-1)[0]?.balance
      if (!currentBalance) return false

      const currentSign = Math.sign(currentBalance)

      return this.chartData.data
        .filter(e => this.$moment(e.period) > this.$moment())
        .find(e => {
          return (
            (currentSign === -1 && e.balance >= 0) ||
            (currentSign !== -1 && e.balance < 0)
          )
        })
    },
    message () {
      if (this.balanceChangeSignPoint) {
        const inDays =
          this.$moment(this.balanceChangeSignPoint.period).diff(
            this.$moment(),
            'days'
          ) + 1
        const date = this.$moment(this.balanceChangeSignPoint.period).format(
          'LL'
        )
        return this.$t(
          this.balanceChangeSignPoint.balance < 0 ? 'cashGapIn' : 'cashGapOut',
          { inDays, date }
        )
      } else {
        return ''
      }
    }
  }
}
</script>

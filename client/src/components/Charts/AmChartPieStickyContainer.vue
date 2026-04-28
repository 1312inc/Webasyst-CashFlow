<template>
  <div
    v-if="$helper.isDesktopEnv"
    class="c-chart-pie-sticky-container"
  >
    <div class="c-chart-pie-sticky-container__inner">
      <AmChartPieSticky v-if="shouldRenderChart" />
    </div>
  </div>
</template>

<script>
import AmChartPieSticky from './AmChartPieSticky'
export default {

  components: {
    AmChartPieSticky
  },
  props: {
    selectedOnlyMode: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    hasTransactions () {
      return this.$store.state.transaction.transactions.data.length > 0
    },
    hasSelectedTransactions () {
      return this.$store.state.transactionBulk.selectedTransactionsIds.length > 0
    },
    shouldRenderChart () {
      return this.hasTransactions && (!this.selectedOnlyMode || this.hasSelectedTransactions)
    }
  }
}
</script>

<style lang="scss">
.c-chart-pie-sticky-container {
  width: 300px;
  flex: none;
  position: sticky;
  top: 4rem;
  align-self: flex-start;

  &__inner {
    margin: 0 auto;
  }
}

@media screen and (max-width: 980px) {
  .c-chart-pie-sticky-container {
    display: none;
  }
}
</style>

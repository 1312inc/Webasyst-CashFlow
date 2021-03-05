<template>
  <div
    v-if="$helper.isDesktopEnv"
    sticky-container
    class="c-chart-pie-sticky-container"
  >
    <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
      <AmChartPieSticky
        v-if="this.$store.state.transaction.transactions.data.length"
      />
      <!-- TODO: Exclude sticky from TransactionControls Component -->
      <TransactionControls
        v-if="$store.state.transactionBulk.selectedTransactionsIds.length"
        direction="column"
        :addView="false"
        class="custom-mt-20"
      />
    </div>
  </div>
</template>

<script>
import AmChartPieSticky from './AmChartPieSticky'
import TransactionControls from '@/components/TransactionControls'
export default {
  components: {
    AmChartPieSticky,
    TransactionControls
  }
}
</script>

<style lang="scss">
.c-chart-pie-sticky-container {
  width: 35%;
  min-widtht: 380px;
  padding-left: 1rem;
}
@media screen and (max-width: 980px) {
  .c-chart-pie-sticky-container {
    display: none;
  }
}
</style>

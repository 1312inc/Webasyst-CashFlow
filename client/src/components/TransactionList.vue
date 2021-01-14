<template>
  <div class="flexbox">
    <div class="width-60">
      <TransactionListUpcoming class="custom-mb-24" />
      <TransactionListIncoming />
    </div>
    <div sticky-container class="width-40 custom-px-24">
      <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
        <AmChartPieSticky />
      </div>
    </div>
  </div>
</template>

<script>
import TransactionListIncoming from '@/components/TransactionListIncoming'
import TransactionListUpcoming from '@/components/TransactionListUpcoming'
import AmChartPieSticky from '@/components/AmChartPieSticky'

export default {
  components: {
    TransactionListIncoming,
    TransactionListUpcoming,
    AmChartPieSticky
  }
}
</script>

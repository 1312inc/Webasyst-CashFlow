<template>
  <div class="flexbox">
    <div class="width-60">
      <TransactionListUpcoming class="custom-mb-24" />
      <TransactionListIncoming />
    </div>
    <AmChartPieStickyContainer class="width-40" />
  </div>
</template>

<script>
import TransactionListIncoming from '@/components/TransactionListIncoming'
import TransactionListUpcoming from '@/components/TransactionListUpcoming'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'

export default {
  components: {
    TransactionListIncoming,
    TransactionListUpcoming,
    AmChartPieStickyContainer
  }
}
</script>

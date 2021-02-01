<template>
  <div class="flexbox">
    <div class="wide">
      <TransactionList />
    </div>
    <AmChartPieStickyContainer class="width-40" />
  </div>
</template>

<script>
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'

export default {
  components: {
    TransactionList,
    AmChartPieStickyContainer
  }
}
</script>

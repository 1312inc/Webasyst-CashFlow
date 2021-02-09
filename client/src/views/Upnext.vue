<template>
  <div>
    <h1>{{ $t("upnext") }}</h1>
    <TransactionControls class="custom-mb-24" />
    <div class="flexbox">
      <div class="wide">
        <TransactionList :observer="false" :reverse="true" :upnext="true" />
      </div>
      <AmChartPieStickyContainer class="width-40" />
    </div>
  </div>
</template>

<script>
import TransactionControls from '@/components/TransactionControls'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    TransactionControls,
    TransactionList,
    AmChartPieStickyContainer
  },

  created () {
    this.$store.dispatch('transaction/fetchUpNextTransactions')
  }
}
</script>

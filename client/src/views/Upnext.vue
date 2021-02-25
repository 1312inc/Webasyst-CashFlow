<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0">{{ $t("upnext") }}</h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList :observer="false" :reverse="true" :upnext="true" />
      </div>
      <AmChartPieStickyContainer class="width-30" />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },

  mounted () {
    this.$store.dispatch('transaction/fetchUpNextTransactions')
  }
}
</script>

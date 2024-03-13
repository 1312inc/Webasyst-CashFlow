<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $t("upnext") }}
        </h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide no-sticky-controls">
        <TransactionList
          :observer="false"
          :upnext="true"
          :show-tomorrow-group="true"
          :show-today-group="false"
        />
      </div>
      <AmChartPieStickyContainer />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },
  mixins: [routerTransitionMixin],

  mounted () {
    this.$store.dispatch('transaction/fetchUpNextTransactions')
  }
}
</script>

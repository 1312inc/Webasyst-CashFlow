<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0">{{ $t("history") }}</h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList :showTodayGroup="false" :showYesterdayGroup="true" />
      </div>
      <AmChartPieStickyContainer class="width-40" />
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
    this.$store.dispatch('transaction/fetchTransactions', {
      from: '',
      to: this.$moment().add(-1, 'd').format('YYYY-MM-DD'),
      offset: 0,
      filter: ''
    })
  }
}
</script>

<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">
          Order {{ $route.params.id }}
        </h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :showFutureGroup="false"
          :showYesterdayGroup="false"
          :showOverdueGroup="false"
          :showTodayGroup="false"
        />
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
      to: this.$moment().format('YYYY-MM-DD'),
      offset: 0,
      filter: `external/shop.${this.$route.params.id}`
    })
  }
}
</script>

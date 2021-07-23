<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">{{ $route.query.text }}</h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList :showTodayGroup="false" :showFutureGroup="false" />
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

  watch: {
    $route: 'makeSearch'
  },

  mounted () {
    this.makeSearch()
  },

  methods: {
    makeSearch () {
      this.$store.dispatch('transaction/fetchTransactions', {
        from: '',
        to: '',
        offset: 0,
        filter: `search/${this.$route.query.text}`
      })
    }
  }

}
</script>

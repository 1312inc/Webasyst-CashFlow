<template>
  <div>
    <ChartHeader />
    <AmChart />
    <DetailsDashboard />
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          @offsetCallback="fetchTransactions"
        />
      </div>
      <AmChartPieStickyContainer class="width-40" />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    AmChart,
    DetailsDashboard,
    TransactionList,
    AmChartPieStickyContainer
  },

  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.updateEntity(to)
      vm.fetchTransactions()
    })
  },

  beforeRouteUpdate (to, from, next) {
    this.updateEntity(to)
    this.fetchTransactions()
    next()
  },

  methods: {
    updateEntity (to) {
      this.$store.dispatch('updateCurrentEntity', {
        name: to.name.toLowerCase(),
        id: +to.params.id || to.params.id
      })
    },

    fetchTransactions () {
      this.$store.dispatch('transaction/fetchTransactions')
    }
  }
}
</script>

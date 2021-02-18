<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <ChartHeaderTitle />
      </template>
      <template v-slot:controls>
        <ChartHeaderControls />
      </template>
    </ChartHeader>
    <AmChart />
    <DetailsDashboard />
    <div class="flexbox">
      <div class="wide">
        <TransactionList />
      </div>
      <AmChartPieStickyContainer class="width-30" />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import ChartHeaderControls from '@/components/ChartHeaderControls'
import ChartHeaderTitle from '@/components/ChartHeaderTitle'
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    ChartHeaderControls,
    ChartHeaderTitle,
    AmChart,
    DetailsDashboard,
    TransactionList,
    AmChartPieStickyContainer
  },

  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.updateEntity(to)
    })
  },

  beforeRouteUpdate (to, from, next) {
    this.updateEntity(to)
    next()
  },

  methods: {
    async updateEntity (to) {
      await this.$store.dispatch('updateCurrentEntity', {
        name: to.name.toLowerCase(),
        id: +to.params.id || to.params.id
      })
      this.$store.dispatch('transaction/fetchTransactions', {
        from: '',
        to: this.$moment().add(1, 'M').format('YYYY-MM-DD'),
        offset: 0
      })
    }
  }
}
</script>

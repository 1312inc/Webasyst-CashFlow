<template>
  <div>
    <ChartHeader />
    <AmChart />
    <DetailsDashboard />
    <TransactionList />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    AmChart,
    DetailsDashboard,
    TransactionList
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
    updateEntity (to) {
      this.$store.commit('transaction/setDetailsInterval', {
        from: '',
        to: '',
        silent: true
      })

      this.$store.dispatch('updateCurrentEntity', {
        name: to.name.toLowerCase(),
        id: +to.params.id || to.params.id
      })
    }
  }
}
</script>

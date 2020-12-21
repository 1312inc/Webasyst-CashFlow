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

export default {
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
      this.$store.dispatch('updateCurrentType', {
        name: to.name.toLowerCase(),
        id: +to.params.id || to.params.id
      })
    }
  }
}
</script>

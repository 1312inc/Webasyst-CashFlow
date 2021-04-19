<template>
  <div>
    <CashGapMessage />
    <ChartHeader>
      <template v-slot:title>
        <ChartHeaderTitle />
      </template>
      <template v-slot:controls>
        <ChartHeaderControls />
      </template>
    </ChartHeader>
    <AmChartContainer />
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
import CashGapMessage from '@/components/CashGapMessage'
import ChartHeader from '@/components/ChartHeader'
import ChartHeaderControls from '@/components/ChartHeaderControls'
import ChartHeaderTitle from '@/components/ChartHeaderTitle'
import AmChartContainer from '@/components/Charts/AmChartContainer'
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    CashGapMessage,
    ChartHeader,
    ChartHeaderControls,
    ChartHeaderTitle,
    AmChartContainer,
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

  beforeRouteLeave (to, from, next) {
    this.$store.commit('setCurrentEntity', { name: '', id: null })
    next()
  },

  methods: {
    async updateEntity (to) {
      const entityName = to.name.toLowerCase()
      const entityId = +to.params.id || to.params.id

      if (entityName !== 'home') {
      // 404 route guard
        if (entityName === 'currency') {
          if (!this.$store.getters['account/currenciesInAccounts'].includes(entityId)) {
            this.$router.replace({ name: 'NotFound' })
            return
          }
        } else {
          if (!this.$store.getters[`${entityName}/getById`](entityId)) {
            this.$router.replace({ name: 'NotFound' })
            return
          }
        }
      }

      await this.$store.dispatch('updateCurrentEntity', {
        name: entityName,
        id: entityId
      })

      // TODO: make current entity more userful
      const currentEntity = this.$store.getters.getCurrentType
      let t = ''
      switch (to.name) {
        case 'Home':
          t = this.$store.state.currentTypeId
          break
        case 'Account':
        case 'Category':
          t = currentEntity.name
          break
        case 'Currency':
          t = to.params.id
          break
      }
      document.title = `${t} — ${this.$helper.accountName}`

      this.$store.dispatch('transaction/fetchTransactions', {
        from: '',
        to: this.$moment().add(1, 'M').format('YYYY-MM-DD'),
        offset: 0
      })
    }
  }
}
</script>

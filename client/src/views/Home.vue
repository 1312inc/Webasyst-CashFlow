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

  async created () {
    const from = this.getDate(
      'from',
      this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    )

    const to = this.getDate(
      'to',
      this.$moment().add(6, 'M').format('YYYY-MM-DD')
    )

    const filter = this.$store.state.transaction.queryParams.filter || `currency/${this.$store.getters['account/currenciesInAccounts'][0]}`

    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams') {
        this.$store.dispatch('transaction/getList')

        const keys = Object.keys(mutation.payload)
        const key = keys[0]
        const changeOffset = keys.length === 1 && key === 'offset'

        if (!changeOffset) {
          this.$store.dispatch('transaction/getChartData')
        }
      }
    })

    this.$store.commit('transaction/updateQueryParams', { from, to, filter })
  },

  beforeDestroy () {
    this.unsubscribe()
  },

  methods: {
    getDate (type, defaultDate) {
      let result = defaultDate
      const lsValue = localStorage.getItem(`interval_${type}`)
      if (lsValue) {
        result = this.$store.state.intervals[type].find((e) => e.key === lsValue)?.value || defaultDate
      }
      return result
    }
  }
}
</script>

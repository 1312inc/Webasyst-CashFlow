<template>
  <div id="wa-app">
    <div class="flexbox">
      <div class="sidebar width-16rem tw-z-50">
        <Sidebar />
      </div>
      <div class="content blank">
        <div class="box contentbox">
          <keep-alive>
            <router-view />
          </keep-alive>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar'
export default {
  components: {
    Sidebar
  },

  async created () {
    await this.$store.dispatch('system/getCurrencies')

    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])

    const from = this.getDate(
      'from',
      this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    )

    const to = this.getDate(
      'to',
      this.$moment().add(6, 'M').format('YYYY-MM-DD')
    )

    const filter = this.$store.state.transaction.queryParams.filter || `currency/${this.$store.getters['account/currenciesInAccounts'][0]}`

    this.$store.subscribe((mutation) => {
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

  methods: {
    getDate (type, defaultDate) {
      let result = defaultDate
      const lsValue = localStorage.getItem(`interval_${type}`)
      if (lsValue) {
        result = this.$store.state.intervals[type].find((e) => e.title === lsValue)?.value || defaultDate
      }
      return result
    }
  }
}
</script>

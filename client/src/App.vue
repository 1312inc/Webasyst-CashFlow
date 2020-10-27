<template>
  <div id="wa-app">
    <div class="flexbox">
      <div class="sidebar width-16rem tw-z-30">
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
    this.$store.watch(
      (state) => state.transaction.queryParams,
      () => {
        this.$store.dispatch('transaction/getList')
        this.$store.dispatch('transaction/getChartData')
      }
    )

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

    this.$store.commit('transaction/updateQueryParams', { from, to })
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

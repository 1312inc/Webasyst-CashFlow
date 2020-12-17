<template>
  <div id="wa-app">
    <div class="flexbox">
      <Sidebar />
      <div class="content blank">
        <div class="box contentbox">
          <!-- <keep-alive> -->
            <router-view />
          <!-- </keep-alive> -->
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

  created () {
    const from = this.getDate(
      'from',
      this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    )

    const to = this.getDate(
      'to',
      this.$moment().add(6, 'M').format('YYYY-MM-DD')
    )

    this.$store.commit('transaction/updateQueryParams', { from, to, silent: true })
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

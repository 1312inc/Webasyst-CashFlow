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

  async mounted () {
    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])

    let from = this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    if (localStorage.getItem('interval_from')) {
      from = this.$store.state.intervals.from.find(e => e.title === localStorage.getItem('interval_from'))?.value || from
    }

    let to = this.$moment().add(6, 'M').format('YYYY-MM-DD')
    if (localStorage.getItem('interval_to')) {
      to = this.$store.state.intervals.to.find(e => e.title === localStorage.getItem('interval_to'))?.value || to
    }

    this.$store.dispatch('transaction/resetAllDataToInterval', { from, to })
  }
}
</script>

<style lang="scss">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>

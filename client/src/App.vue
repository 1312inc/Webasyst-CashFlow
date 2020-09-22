<template>
  <div id="app">
    <!-- <div id="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link> |
      <a href="/webasyst/cash/?module=static">Static page</a>
    </div> -->
    <div class="flex">
      <div class="w-1/6 bg-gray-100">
        <Sidebar />
      </div>
      <div class="w-5/6 mx-10">
        <keep-alive>
          <router-view/>
        </keep-alive>
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
  mounted () {
    this.$store.dispatch('transaction/getList', { from: '2020-07-15', to: '2020-09-15' })
    this.$store.dispatch('transaction/getChartData')
    this.$store.dispatch('category/getList')
    this.$store.dispatch('account/getList')
  }
}
</script>

<style lang="scss">
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>

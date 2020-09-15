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
    this.$store.dispatch('transaction/getList', { from: '2019-01-01', to: '2020-09-08' })
    this.$store.dispatch('category/getList')
    this.$store.dispatch('account/getList')
  }
}
</script>

<style lang="scss" scoped>
  #app {
    text-align: center;
    padding-top: 4rem;
  }
  h2 {
    margin-bottom: 4rem;
  }
</style>

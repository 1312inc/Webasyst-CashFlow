<template>
  <div id="app">
    <div id="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link> |
      <a href="/webasyst/cash/?module=static">Static page</a>
    </div>
    <keep-alive>
      <router-view/>
    </keep-alive>
  </div>
</template>

<script>
export default {
  mounted () {
    this.$store.dispatch('transaction/getList', { from: '2019-01-01', to: '2020-09-08' })
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

<template>
  <div id="wa-app">
    <div class="flexbox">
      <div class="sidebar">
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

    this.$store.commit('setCurrentType', {
      name: this.$route.name,
      id: +this.$route.params.id
    })

    this.$store.dispatch('transaction/resetAllDataToInterval', {
      from: this.$moment().add(-1, 'M').format('YYYY-MM-DD'),
      to: this.$moment().format('YYYY-MM-DD')
    })
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

<template>
  <div id="app">
    <div class="flex">
      <div class="w-1/6 bg-gray-100">
        <Sidebar />
      </div>
      <div class="w-5/6 mx-10">
        <keep-alive>
          <router-view />
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

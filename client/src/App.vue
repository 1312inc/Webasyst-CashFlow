<template>
  <div id="wa-app">
    <div class="flexbox">
      <Sidebar />
      <div class="content blank">
        <div class="box contentbox">
          <!-- <keep-alive> -->
            <router-view v-if="showView" />
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

  data () {
    return {
      showView: false
    }
  },

  async created () {
    await this.$store.dispatch('system/getCurrencies')
    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])
    this.showView = true
  }

}
</script>

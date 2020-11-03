<template>
  <Sidebar />
</template>

<script>
import Sidebar from '@/components/Sidebar'
export default {
  components: {
    Sidebar
  },

  async created () {
    await this.$store.dispatch('system/getCurrencies')
    this.$store.dispatch('account/getList')
    this.$store.dispatch('category/getList')
  }

}
</script>

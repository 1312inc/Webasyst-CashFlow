<template>
  <div id="app" class="p-4">
    <router-view />
  </div>
</template>

<script>

export default {
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

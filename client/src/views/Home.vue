<template>
  <div class="home">
    <h1>This is an home page</h1>

    {{items}}
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  computed: mapState(['items']),
  mounted () {
    this.$store.dispatch('get')
  }
}
</script>

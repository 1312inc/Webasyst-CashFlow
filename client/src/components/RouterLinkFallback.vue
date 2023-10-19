<template>
  <a :href="url"><slot /></a>
</template>

<script>
const baseUrl = window?.appState?.baseUrl || '/'
export default {
  props: {
    to: {
      type: String
    }
  },

  computed: {
    url () {
      return baseUrl.slice(0, -1) + this.to
    }
  }
}
</script>

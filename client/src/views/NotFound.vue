<template>
  <div class="custom-p-20">
    <h1>404</h1>
    <h3>{{ $t('404.title') }}</h3>
  </div>
</template>

<script>
export default {
  metaInfo () {
    return {
      title: this.$t('404.title'),
      titleTemplate: `%s – ${window.appState?.accountName || ''}`
    }
  }
}
</script>

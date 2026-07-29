<template>
  <div class="custom-p-20">
    <h1>404</h1>
    <h3>{{ $t('404.title') }}</h3>
  </div>
</template>

<script>
import { appStateService } from '@/services/appState'

export default {
  metaInfo () {
    return {
      title: this.$t('404.title'),
      titleTemplate: appStateService.titleTemplate
    }
  }
}
</script>

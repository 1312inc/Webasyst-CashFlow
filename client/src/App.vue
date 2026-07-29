<template>
  <component
    :is="appStateService.isDesktop ? DialogProvider : 'div'"
    class="content custom-pb-24 custom-ml-12 custom-mr-24 custom-mr-12-mobile"
  >
    <router-view />
    <TransactionModals />
  </component>
</template>

<script setup>
import DialogProvider from './components/DialogProvider.vue'
import { appStateService } from './services/appState'
import { defineAsyncComponent } from 'vue'

const TransactionModals = defineAsyncComponent(() => import('./components/TransactionModals.vue'))
</script>

<style lang="scss">
@import "microtip/microtip";
</style>

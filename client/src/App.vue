<template>
  <DialogProvider class="content custom-pb-24 custom-ml-12 custom-mr-24 custom-mr-12-mobile">
    <router-view />
    <TransactionModals />
  </DialogProvider>
</template>

<script>
import DialogProvider from './components/DialogProvider.vue'

export default {
  components: {
    DialogProvider,
    TransactionModals: () => import('./components/TransactionModals.vue')
  }
}
</script>

<style lang="scss">
@import "microtip/microtip";
</style>

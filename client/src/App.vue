<template>
  <DialogProvider class="content blank custom-pb-24 custom-pl-8 custom-pl-0-mobile">
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

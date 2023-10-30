<template>
  <DialogProvider class="content blank custom-pb-24 custom-pl-8 custom-pl-0-mobile">
    <router-view />
    <!-- <TransactionModals /> -->
  </DialogProvider>
</template>

<script setup>
import DialogProvider from './components/DialogProvider.vue'
// import TransactionModals from './components/TransactionModals.vue'
</script>

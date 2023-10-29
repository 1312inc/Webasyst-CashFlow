<template>
  <DialogProvider class="content blank custom-pb-24">
    <router-view />
    <!-- <TransactionModals /> -->
  </DialogProvider>
</template>

<script setup>
import DialogProvider from './components/DialogProvider.vue'
// import TransactionModals from './components/TransactionModals.vue'
</script>

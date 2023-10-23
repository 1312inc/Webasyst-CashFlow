<template>
  <DialogProvider class="content hide-scrollbar blank">
    <div
      sticky-container
      class="box custom-px-8 custom-p-0-mobile"
    >
      <router-view />
    </div>
  </DialogProvider>
</template>

<script setup>
import DialogProvider from './components/DialogProvider.vue'
</script>

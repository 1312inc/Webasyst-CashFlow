<template>
  <div class="content custom-pb-24 custom-ml-12 custom-mr-24 custom-mr-12-mobile">
    <router-view />
    <TransactionModals />
  </div>
</template>

<script>

export default {
  components: {
    TransactionModals: () => import('./components/TransactionModals.vue')
  }
}
</script>

<style lang="scss">
@import "microtip/microtip";
</style>

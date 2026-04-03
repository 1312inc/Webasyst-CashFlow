<template>
  <div class="box blank rounded custom-mb-24">
    <slot />
  </div>
</template>

<style scoped>
  .box:empty {
    display: none;
  }
</style>

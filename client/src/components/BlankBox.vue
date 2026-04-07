<script setup>
const props = defineProps(['disableBottomMargin'])
</script>

<template>
  <div
    class="box blank rounded"
    :class="{'custom-mb-24': !props.disableBottomMargin}"
  >
    <slot />
  </div>
</template>

<style scoped>
  .box {
    position: relative;
    box-sizing: border-box;
  }

  .box:empty {
    display: none;
  }
</style>

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
    padding: 0;
    overflow: clip;
  }
  .box.rounded {
    border-radius: 0.75rem;
  }

  .box:empty {
    display: none;
  }
</style>

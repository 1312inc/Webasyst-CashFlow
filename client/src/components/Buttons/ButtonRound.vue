<template>
  <button
    class="button rounded"
    :aria-label="tooltip"
    data-microtip-position="top"
    role="tooltip"
  >
    <i :class="`fas ${icon}`" />
  </button>
</template>

<script>
export default {
  props: ['icon', 'tooltip']
}
</script>

<style scoped lang="scss">
@import "microtip/microtip";
.button {
  width: 42px;
  height: 42px;
  padding: 0;
}
</style>

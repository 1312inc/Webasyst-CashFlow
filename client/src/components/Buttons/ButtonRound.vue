<template>
  <button
    class="button rounded light-gray"
    :aria-label="tooltip"
    data-microtip-position="top"
    role="tooltip"
  >
    <i :class="`fas ${icon}`"></i>
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
  color: var(--gray);

  &:hover {
    color: var(--light-gray);
  }
}
</style>

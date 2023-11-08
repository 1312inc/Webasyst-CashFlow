<template>
  <div
    ref="observer"
    class="collapse-container"
  >
    <div ref="observable">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  mounted () {
    this.resizeObserver = new ResizeObserver(entries => {
      for (const entry of entries) {
        if (entry.contentBoxSize) {
          this.$refs.observer.style.height =
            entry.contentBoxSize[0].blockSize + 'px'
        } else {
          this.$refs.observer.style.height = entry.contentRect.height + 'px'
        }
      }
    })

    this.resizeObserver.observe(this.$refs.observable)
  },

  beforeDestroy () {
    this.resizeObserver.unobserve(this.$refs.observable)
  }
}
</script>

<style>
.collapse-container {
  transition: 0.3s ease-out;
  overflow: hidden;
}
</style>

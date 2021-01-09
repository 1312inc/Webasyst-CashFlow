<template>
  <div>
    <div class="fa-2x flexbox opacity-40 c-spinner">
      <i class="fas fa-circle-notch fa-spin"></i>
    </div>
  </div>
</template>

<script>
export default {
  mounted () {
    this.observer = new IntersectionObserver(([entry]) => {
      if (entry && entry.isIntersecting) {
        this.$emit('callback')
      }
    })
    this.observer.observe(this.$el)
  },

  beforeDestroy () {
    this.observer.disconnect()
  }
}
</script>

<style>
.c-spinner {
  justify-content: center;
}
</style>

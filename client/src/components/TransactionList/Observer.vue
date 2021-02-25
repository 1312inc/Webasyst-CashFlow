<template>
  <SkeletonTransaction :lines="1" />
</template>

<script>
import SkeletonTransaction from './SkeletonTransaction'
export default {
  components: {
    SkeletonTransaction
  },
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

<template>
  <transition
    name="fade"
    appear
  >
    <div class="dialog">
      <div class="dialog-background" />
      <transition
        name="slide-fade"
        appear
      >
        <slot />
      </transition>
    </div>
  </transition>
</template>

<script>
export default {
  mounted () {
    document.body.classList.add('modal')
    document.addEventListener('keyup', this.handler)
  },

  beforeDestroy () {
    document.body.classList.remove('modal')
    document.removeEventListener('keyup', this.handler)
  },

  methods: {
    handler (evt) {
      if (evt.keyCode === 27) {
        this.$emit('close')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.dialog {
  display: block;
  z-index: 99999;

  &:deep .dialog-content {
    min-height: auto;
    max-height: calc(100vh - 220px);
  }
  &:deep .dialog-body {
    top: 50%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
    max-height: 100%;
    overflow-y: auto;

    @media screen and (max-width: 768px) {
      transform: none;
    }
  }

}
</style>

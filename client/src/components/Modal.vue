<template>
  <transition name="fade" appear>
    <div class="dialog">
      <div class="dialog-background"></div>
      <transition name="slide-fade" appear>
        <div class="dialog-body">
          <div class="dialog-content">
            <slot></slot>
          </div>
        </div>
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

<style scoped>
.dialog {
  display: block;
}

.dialog .dialog-body {
  top: 50%;
  left: 50%;
  transform: translateX(-50%) translateY(-50%);
}
</style>

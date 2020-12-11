<template>
  <transition name="fade">
    <div class="dialog">
      <transition name="fade" appear>
        <div class="dialog-background"></div>
      </transition>
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
  created () {
    document.body.classList.add('modal')
  },

  beforeDestroy () {
    document.body.classList.remove('modal')
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

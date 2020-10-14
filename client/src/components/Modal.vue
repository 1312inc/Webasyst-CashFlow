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
export default {}
</script>

<style>
.dialog {
  display: block;
}

.dialog .dialog-body {
  top: 50%;
  left: 50%;
  transform: translateX(-50%) translateY(-50%);
}

.slide-fade-enter-active {
  transition: all 0.2s;
}

.slide-fade-enter, .slide-fade-leave-to {
  transform: translateX(-50%) translateY(-44%) !important;
  opacity: 0;
}
</style>

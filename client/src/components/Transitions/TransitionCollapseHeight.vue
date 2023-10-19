<template>
  <transition
    name="collapse"
    @before-enter="beforeEnter"
    @enter="enter"
    @before-leave="beforeLeave"
    @leave="leave"
  >
    <slot />
  </transition>
</template>

<script>
export default {
  methods: {
    beforeEnter: el => {
      el.style.height = '0'
    },
    enter: el => {
      el.style.height = el.scrollHeight + 'px'
    },
    beforeLeave: el => {
      el.style.height = el.scrollHeight + 'px'
    },
    leave: el => {
      el.style.height = '0'
    }
  }
}
</script>

<style>
.collapse-enter-active,
.collapse-leave-active {
  transition: 0.3s ease-out;
  overflow: hidden;
}
</style>

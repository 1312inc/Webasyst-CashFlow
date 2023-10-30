<script setup>
import { useFloating } from '@floating-ui/vue'
import { ref } from 'vue'

const open = ref(false)
const floating = ref(null)
const reference = ref(null)
const { floatingStyles } = useFloating(reference, floating, {
  placement: 'bottom-start'
})
</script>

<template>
  <div
    ref="reference"
    @mouseover.prevent.stop="open = true"
    @mouseleave.prevent.stop="open = false"
  >
    <slot name="toggler" />
    <div
      v-if="open"
      ref="floating"
      class="dropdown is-opened"
      style="z-index: 9999;"
      :style="floatingStyles"
    >
      <div
        class="dropdown-body"
        style="min-width: 250px;"
      >
        <slot />
      </div>
    </div>
  </div>
</template>

<style scoped>
  button {
    margin: 0;
  }
</style>

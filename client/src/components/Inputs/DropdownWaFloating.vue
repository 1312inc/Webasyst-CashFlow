<script setup>
import {
  useFloating, flip,
  shift
} from '@floating-ui/vue'
import { ref } from 'vue'

const props = defineProps(['strategy', 'hideOnMobile'])

const open = ref(false)
const floating = ref(null)
const reference = ref(null)
const { floatingStyles } = useFloating(reference, floating, {
  placement: 'bottom-start',
  strategy: props.strategy ?? 'absolute',
  middleware: [flip(), shift()]
})
</script>

<template>
  <div
    ref="reference"
    @mouseover.prevent.stop="() => { if (!(hideOnMobile && $helper.isTabletMediaQuery())) { open = true } }"
    @mouseleave.prevent.stop="() => { if (!(hideOnMobile && $helper.isTabletMediaQuery())) { open = false } }"
  >
    <slot name="toggler" />
    <div
      v-if="open"
      ref="floating"
      class="dropdown is-opened"
      :class="{ 'no-pointer': props.strategy === 'fixed' }"
      :style="floatingStyles"
    >
      <div
        class="dropdown-body"
        style="min-width: 200px;"
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

.dropdown {
  z-index: 9999;
}

.dropdown.no-pointer {
  pointer-events: none;
}
</style>

<style scoped>
.dropdown-body {
  position: relative;
  display: block;
  left: auto;
  top: auto;
}
</style>

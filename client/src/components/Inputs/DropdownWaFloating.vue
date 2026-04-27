<script setup>
import { ref, onMounted } from 'vue'

const floating = ref(null)
const reference = ref(null)

onMounted(async () => {
  const tippy = await waitForTippy()

  if (!tippy || !floating.value || !reference.value) return

  tippy(reference.value, {
    content: floating.value,
    interactive: true,
    placement: 'bottom-start',
    appendTo: () => document.body,
    theme: 'transparent',
    arrow: false,
    offset: [0, 0]
  })
})

function waitForTippy () {
  return new Promise((resolve) => {
    const check = () => {
      if (window.tippy) resolve(window.tippy)
      else setTimeout(check, 100)
    }
    check()
  })
}

</script>

<template>
  <div>
    <div ref="reference">
      <slot name="toggler" />
    </div>
    <div
      ref="floating"
      class="dropdown is-opened"
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

<style>
[data-theme~='transparent'] {
  background-color: transparent;
  color: transparent;
  padding: 0 !important;
  margin: 0 !important;
  border-radius: 0;
  border: 0;
  box-shadow: 0 !important;
}
[data-theme~='transparent'] .tippy-arrow {
  width: 0;
  height: 0;
}
[data-theme~='transparent'] .wa-tooltip-content {
  padding: 0 !important;
  margin: 0 !important;
}

</style>

<style scoped>
button {
  margin: 0;
}

.dropdown {
  z-index: 9999;
}

.dropdown-body {
  position: relative;
  left: auto;
  top: auto;
}

</style>

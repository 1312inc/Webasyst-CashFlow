<script setup>
import { ref, onMounted } from 'vue'

const tippy = window.tippy

const floating = ref(null)
const reference = ref(null)

onMounted(() => {
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

</script>

<template>
  <div>
    <div ref="reference">
      <slot name="toggler" />
    </div>
    <div
      ref="floating"
      class="dropdown"
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
.tippy-box[data-theme~='transparent'] {
  background-color: transparent;
  color: transparent;
  border: none;
  box-shadow: none;
  padding: 0;
  margin: 0;
  border-radius: 0;
  border: 0;
  box-shadow: 0;
  padding: 0;
}
.tippy-box[data-theme~='transparent'] .tippy-arrow {
  width: 0;
  height: 0;
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

[data-tippy-root] .dropdown-body {
  display: block;
}
</style>

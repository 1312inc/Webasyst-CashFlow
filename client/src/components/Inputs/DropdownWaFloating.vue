<script setup>
import { ref, onMounted } from 'vue'
import { waitForTippy } from '@/utils/waiters'

const floating = ref(null)
const reference = ref(null)
const tippyInstance = ref(null)

function hide () {
  tippyInstance.value?.hide()
}

onMounted(async () => {
  const tippy = await waitForTippy()

  if (!tippy || !floating.value || !reference.value) return

  tippyInstance.value = tippy(reference.value, {
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
      class="dropdown is-opened"
    >
      <div
        class="dropdown-body"
        style="min-width: 200px;"
      >
        <slot
          :close="hide"
        />
      </div>
    </div>
  </div>
</template>

<style>
[data-theme~='transparent'] {
  background-color: transparent !important;
  padding: 0 !important;
  margin: 0 !important;
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
.wa-tooltip-box[data-theme~='transparent'] {
  line-height: 0;
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

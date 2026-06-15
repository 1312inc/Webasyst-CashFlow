<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { waitForTippy } from '@/utils/waiters'
import { appStateService } from '@/services/appState'

const floating = ref(null)
const reference = ref(null)
const tippyInstance = ref(null)
const isInitialized = ref(false)
const isWebView = appStateService.webView

function hide () {
  tippyInstance.value?.hide()
}

function toggle () {
  if (!isWebView || !tippyInstance.value) return

  if (tippyInstance.value.state.isVisible) {
    tippyInstance.value.hide()
  } else {
    tippyInstance.value.show()
  }
}

onMounted(async () => {
  const tippy = await waitForTippy()

  if (!tippy || !floating.value || !reference.value) return

  tippyInstance.value = tippy(reference.value, {
    content: floating.value,
    trigger: isWebView ? 'manual' : 'click mouseenter',
    interactive: true,
    hideOnClick: true,
    placement: 'bottom-start',
    appendTo: () => document.body,
    theme: 'transparent',
    arrow: false,
    offset: [0, 0],
    onCreate () {
      isInitialized.value = true
    }
  })
})

onBeforeUnmount(() => {
  tippyInstance.value?.destroy()
})

</script>

<template>
  <div>
    <div
      ref="reference"
      @click="toggle"
    >
      <slot name="toggler" />
    </div>
    <div
      v-show="isInitialized"
      ref="floating"
      class="dropdown is-opened"
    >
      <div
        class="dropdown-body"
        style="min-width: 280px;"
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

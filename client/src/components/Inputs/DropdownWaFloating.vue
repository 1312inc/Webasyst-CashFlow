<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { waitForTippy } from '@/utils/waiters'
import { appStateService } from '@/services/appState'

const floating = ref(null)
const reference = ref(null)
const tippyInstance = ref(null)
const isInitialized = ref(false)
const isWebView = appStateService.webView
const nativeViewport = appStateService.nativeViewport

function viewportPadding () {
  const css = getComputedStyle(document.documentElement)
  const inset = edge => Number.parseFloat(css.getPropertyValue(`--cash-host-${edge}`)) || 0
  return {
    top: inset('top') + 8,
    right: inset('right') + 8,
    bottom: Math.max(inset('bottom'), inset('keyboard')) + 8,
    left: inset('left') + 8
  }
}

function updatePlacement () {
  const instance = tippyInstance.value
  if (!instance || !nativeViewport) return
  const padding = viewportPadding()
  const viewport = window.visualViewport
  const width = viewport?.width ?? window.innerWidth
  const height = viewport ? Math.min(window.innerHeight - padding.bottom, viewport.height + viewport.offsetTop - 8) : window.innerHeight - padding.bottom
  floating.value.style.setProperty('--cash-menu-width', `${Math.max(1, width - padding.left - padding.right)}px`)
  floating.value.style.setProperty('--cash-menu-height', `${Math.max(1, height - padding.top)}px`)
  instance.setProps({
    popperOptions: {
      modifiers: [
        { name: 'flip', options: { padding } },
        { name: 'preventOverflow', options: { padding, altAxis: true } }
      ]
    }
  })
}

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
  if (nativeViewport) {
    updatePlacement()
    document.addEventListener('cashflowviewportchange', updatePlacement)
    window.visualViewport?.addEventListener('resize', updatePlacement)
  }
})

onBeforeUnmount(() => {
  if (nativeViewport) {
    document.removeEventListener('cashflowviewportchange', updatePlacement)
    window.visualViewport?.removeEventListener('resize', updatePlacement)
  }
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
      :class="{ 'dropdown--native-viewport': nativeViewport }"
    >
      <div
        class="dropdown-body"
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
  min-width: 280px;
  position: relative;
  left: auto;
  top: auto;
}

.dropdown--native-viewport .dropdown-body {
  min-width: min(280px, var(--cash-menu-width, 100vw));
  max-width: var(--cash-menu-width, 100vw);
  max-height: var(--cash-menu-height, 70vh);
  overflow: auto;
  overscroll-behavior: contain;
}

</style>

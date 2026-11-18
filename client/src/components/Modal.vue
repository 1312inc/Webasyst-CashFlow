<template>
  <transition
    name="fade"
    appear
    :css="!disableTransition"
  >
    <div class="dialog" :class="{ 'dialog--webview': nativeDialog || fullViewport, 'dialog--full-viewport': fullViewport }">
      <div class="dialog-background" />
      <transition
        name="slide-fade"
        appear
        :css="!disableTransition && !fullViewport"
      >
        <slot />
      </transition>
    </div>
  </transition>
</template>

<script>
import { appStateService } from '@/services/appState'

export default {
  computed: {
    nativeDialog () {
      return appStateService.nativeDialog
    },
    fullViewport () {
      return appStateService.nativeViewport
    }
  },

  props: {
    disableTransition: {
      type: Boolean,
      default: false
    }
  },

  mounted () {
    document.body.classList.add('modal')
    document.addEventListener('keyup', this.handler)
    // Tippy dropdowns are portaled to body with a higher z-index than the dialog.
    // Hide after the opening click has finished, otherwise the menu stays on top.
    this.hideFloatingTooltips()
    setTimeout(this.hideFloatingTooltips)
    if (this.fullViewport && typeof ResizeObserver === 'function') {
      const footer = this.$el.querySelector('.dialog-footer')
      if (footer) {
        const update = () => this.$el.style.setProperty('--cash-dialog-actions-height', `${footer.offsetHeight}px`)
        this.footerResizeObserver = new ResizeObserver(update)
        this.footerResizeObserver.observe(footer)
        update()
      }
    }
  },

  beforeDestroy () {
    this.footerResizeObserver?.disconnect()
    document.body.classList.remove('modal')
    document.removeEventListener('keyup', this.handler)
  },

  methods: {
    hideFloatingTooltips () {
      window.tippy?.hideAll?.()
    },

    handler (evt) {
      if (evt.keyCode === 27) {
        this.$emit('close')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.dialog {
  display: block;
  z-index: 99999;

  &:deep .dialog-content {
    min-height: auto;
    max-height: calc(100vh - 220px);
  }
  &:deep .dialog-body {
    top: 50%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
    max-height: 100%;
    overflow-y: auto;

    @media screen and (max-width: 768px) {
      transform: none;
    }
  }

  &.dialog--webview {
    overflow: hidden;

    &:deep .dialog-body {
      inset: 0;
      display: flex;
      flex-direction: column;
      width: 100%;
      max-width: none;
      max-height: none;
      overflow: hidden;
      border-radius: 0;
      transform: none;
    }

    &:deep .dialog-header,
    &:deep .dialog-footer {
      flex-shrink: 0;
    }

    &:deep .dialog-content {
      flex: 1 1 auto;
      min-height: 0;
      max-height: none;
      overflow-y: auto;
      overscroll-behavior: contain;
      scroll-padding-block: 12px;
    }

    @media (max-height: 420px) {
      &:deep .dialog-header {
        padding-block: 0.75rem;
      }

      &:deep .dialog-footer {
        margin-top: 0;
        padding-block: 0.75rem;
      }
    }
  }

  &.dialog--full-viewport {
    &.dialog--webview:deep .dialog-body {
      display: block;
      height: 100%;
      overflow-y: auto;
      overscroll-behavior: contain;
      scroll-padding: calc(var(--cash-safe-top) + 12px) var(--cash-content-right)
        calc(var(--cash-visible-bottom) + var(--cash-dialog-actions-height, 0px) + 12px) var(--cash-content-left);
      padding: var(--cash-safe-top) var(--cash-content-right)
        calc(var(--cash-visible-bottom) + var(--cash-dialog-actions-height, 0px)) var(--cash-content-left);
    }

    &.dialog--webview:deep .dialog-content {
      overflow: visible;
    }

    &.dialog--webview:deep .dialog-footer {
      position: fixed;
      right: 0;
      bottom: var(--cash-visible-bottom);
      left: 0;
      margin-top: 0;
      background: var(--background-color-blank);
      padding: 0.75rem calc(1.5rem + var(--cash-content-right))
        0.75rem calc(1.5rem + var(--cash-content-left));
    }

    // The keyboard and its accessory can show the WebView beneath them.
    &.dialog--webview:deep .dialog-footer::after {
      content: '';
      position: absolute;
      top: 100%;
      inset-inline: 0;
      height: var(--cash-visible-bottom);
      background: inherit;
    }
  }
}
</style>

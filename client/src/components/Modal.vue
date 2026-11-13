<template>
  <transition
    name="fade"
    appear
    :css="!disableTransition"
  >
    <div class="dialog" :class="{ 'dialog--webview': nativeDialog }">
      <div class="dialog-background" />
      <transition
        name="slide-fade"
        appear
        :css="!disableTransition"
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
  },

  beforeDestroy () {
    document.body.classList.remove('modal')
    document.removeEventListener('keyup', this.handler)
  },

  methods: {
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
}
</style>

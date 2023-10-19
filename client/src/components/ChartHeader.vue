<template>
  <div class="c-header custom-p-0-mobile">
    <div
      class="flexbox wrap-mobile full-width"
      sticky-ref="controls"
    >
      <div class="width-100">
        <slot
          v-if="$helper.isDesktopEnv"
          name="title"
        />
        <div
          v-if="showStickyHeader"
          v-sticky="$helper.isDesktopEnv"
          :sticky-offset="`{top: ${$helper.isHeader() ? 64 : 0}}`"
          sticky-z-index="12"
          sticky-width-ref="controls"
          class="c-sticky-header-controls"
          :class="!$helper.isDesktopEnv && 'c-sticky-header-controls--mobile'"
        >
          <TransactionControls
            :multiselect-view="!$helper.isDesktopEnv"
            class="custom-px-16-mobile"
          />
        </div>
      </div>
      <slot name="controls" />
    </div>
  </div>
</template>

<script>
import TransactionControls from '@/components/TransactionControls'
export default {

  components: {
    TransactionControls
  },
  props: {
    showControls: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    showStickyHeader () {
      return (
        ((!this.$helper.isDesktopEnv &&
          this.$store.state.transactionBulk.selectedTransactionsIds.length) ||
        this.$helper.isDesktopEnv) && this.showControls
      )
    }
  }
}
</script>

<style lang="scss">
.c-sticky-header-controls {
  &.top-sticky:before {
    position: absolute;
    top: 0;
    left: -2rem;
    right: -2rem;
    bottom: 0;
    display: block;
    content: " ";
    z-index: -1;
    background-color: var(--background-color-blank);
  }

  &--mobile {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 40;
    background-color: var(--background-color-blank);
  }
}
</style>

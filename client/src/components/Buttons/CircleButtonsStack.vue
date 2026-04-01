<template>
  <div
    class="c-circle-buttons-stack"
    :style="{
      '--c-size': `${sizePx}px`,
      '--c-gap': `${gapPx}px`,
      '--c-overlap': `${overlapPx}px`,
    }"
  >
    <button
      v-for="btn in normalizedButtons"
      :key="btn.key"
      class="circle c-circle-buttons-stack__btn"
      :class="btn.buttonClass"
      type="button"
      :aria-label="$t(btn.type)"
      data-microtip-position="bottom"
      role="tooltip"
      @click="$emit('click', btn.type)"
    >
      <span class="icon">
        <i :class="btn.iconClass" />
      </span>
    </button>
  </div>
</template>

<script>
export default {
  name: 'CircleButtonsStack',
  props: {
    buttons: {
      type: Array,
      default: () => ([
        { key: 'plus', buttonClass: 'c-button-add-income', iconClass: 'fas fa-plus', type: 'income' },
        { key: 'minus', buttonClass: 'c-button-add-expense', iconClass: 'fas fa-minus', type: 'expense' },
        { key: 'more', buttonClass: 'light-gray', iconClass: 'fas fa-ellipsis-h', type: 'transfer' }
      ])
    },
    sizePx: {
      type: Number,
      default: 42
    },
    gapPx: {
      type: Number,
      default: 0
    },
    overlapPx: {
      type: Number,
      default: 26
    }
  },
  computed: {
    normalizedButtons () {
      return (this.buttons || [])
        .filter(Boolean)
        .map((b, idx) => ({
          key: b.key ?? String(idx),
          buttonClass: b.buttonClass,
          iconClass: b.iconClass ?? 'fas fa-circle',
          type: b.type
        }))
    }
  }
}
</script>

<style scoped lang="scss">
.c-circle-buttons-stack {
  --c-size: 42px;
  --c-gap: 10px;
  --c-overlap: 26px;

  display: inline-flex;
  align-items: center;
  height: var(--c-size);
}

.c-circle-buttons-stack__btn {
  width: var(--c-size);
  height: var(--c-size);
  padding: 0;
  transition: transform 180ms ease, box-shadow 180ms ease;

  &:not(:first-child) {
    margin-left: calc(var(--c-overlap) * -1);
  }
}

.c-circle-buttons-stack:hover .c-circle-buttons-stack__btn {
  margin-left: 0;
}

.c-circle-buttons-stack:hover .c-circle-buttons-stack__btn + .c-circle-buttons-stack__btn {
  margin-left: var(--c-gap);
}

.c-circle-buttons-stack__btn:nth-child(1) {
  z-index: 3;
}
.c-circle-buttons-stack__btn:nth-child(2) {
  z-index: 2;
}
.c-circle-buttons-stack__btn:nth-child(3) {
  z-index: 1;
}

.c-circle-buttons-stack:hover .c-circle-buttons-stack__btn {
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
</style>

<template>
  <div>
    <div
      v-if="value"
      @click="$emit('input', null)"
      class="c-icon-item selected"
    >
      <i :class="value"></i>
    </div>
    <div v-show="!value" class="c-icon-list">
      <div
        v-for="(icon, i) in $options.icons"
        :key="i"
        @click="$emit('input', icon)"
        :class="{ selected: i === $options.icons.indexOf(value) }"
        class="c-icon-item"
      >
        <i :class="icon"></i>
      </div>
    </div>
  </div>
</template>
<script>
const icons = [
  'fas fa-user-friends',
  'fas fa-user-tie',
  'fas fa-hat-cowboy',
  'fas fa-stethoscope',
  'fas fa-syringe',
  'fas fa-child',
  'fas fa-mask',
  'fas fa-male',
  'fas fa-female',
  'fas fa-tools',
  'fas fa-hard-hat',
  'fas fa-users',
  'fas fa-lock',
  'fas fa-unlock',
  'fas fa-file',
  'fas fa-broom',
  'fas fa-star',
  'fas fa-pencil-alt',
  'fas fa-bolt',
  'fas fa-lightbulb',
  'fas fa-images',
  'fas fa-chart-bar',
  'fas fa-book',
  'fas fa-map-marker-alt',
  'fas fa-anchor',
  'fas fa-car',
  'fas fa-save',
  'fas fa-cookie',
  'fas fa-radiation-alt',
  'fas fa-bug',
  'fas fa-clock',
  'fas fa-coffee',
  'fas fa-home',
  'fas fa-apple-alt',
  'fas fa-briefcase',
  'fas fa-guitar',
  'fas fa-grin',
  'fas fa-futbol',
  'fas fa-bullseye',
  'fas fa-award',
  'fas fa-phone',
  'fas fa-user-clock',
  'fas fa-paw',
  'fas fa-seedling',
  'fas fa-video',
  'fas fa-store'
]

export default {
  icons,
  props: {
    value: {
      type: String
    }
  }
}
</script>

<style lang="scss">
.c-icon-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45em;
}
.c-icon-item {
  border-radius: 50%;
  width: 2.25em;
  height: 2.25em;
  display: inline-grid;
  place-items: center;
  color: var(--menu-glyph-color);
  margin: 0 !important;

  &:hover,
  &.selected {
    background-color: var(--menu-selected-background-color);
    color: var(--black);
    cursor: pointer;
  }
}
</style>

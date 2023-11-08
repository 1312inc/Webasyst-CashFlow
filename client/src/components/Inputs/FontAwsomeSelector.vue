<template>
  <div>
    <div
      v-if="!open"
      class="flexbox middle space-8"
    >
      <div>
        <div
          class="c-icon-item"
          :style="`color: ${color};`"
          @click="open = true"
        >
          <i :class="value || $options.icons[0]" />
        </div>
      </div>
      <div>
        <a
          class="small"
          href="#"
          @click.prevent="open = true"
        >{{
          $t("change")
        }}</a>
      </div>
    </div>
    <div
      v-show="open"
      class="c-icon-list"
    >
      <div
        v-for="(icon, i) in $options.icons"
        :key="i"
        class="c-icon-item"
        :style="`color: ${color}`"
        @click="
          $emit('input', i === 0 ? null : icon);
          open = false;
        "
      >
        <i :class="icon" />
      </div>
    </div>
  </div>
</template>
<script>
const icons = [
  'fas fa-circle',
  'fas fa-user-tie',
  'fas fa-flag',
  'fas fa-syringe',
  'fas fa-child',
  'fas fa-mask',
  'fas fa-tools',
  'fas fa-hard-hat',
  'fas fa-lock',
  'fas fa-unlock',
  'fas fa-file',
  'fas fa-broom',
  'fas fa-star',
  'fas fa-pencil-alt',
  'fas fa-bolt',
  'fas fa-lightbulb',
  'fas fa-camera',
  'fas fa-chart-bar',
  'fas fa-book',
  'fas fa-map-marker-alt',
  'fas fa-anchor',
  'fas fa-car',
  'fas fa-balance-scale',
  'fas fa-radiation-alt',
  'fas fa-bug',
  'fas fa-clock',
  'fas fa-coffee',
  'fas fa-home',
  'fas fa-carrot',
  'fas fa-briefcase',
  'fas fa-music',
  'fas fa-grin',
  'fas fa-table-tennis',
  'fas fa-phone',
  'fas fa-paw',
  'fas fa-seedling',
  'fas fa-video',
  'fas fa-store',
  'fas fa-crown',
  'fas fa-church',
  'fas fa-piggy-bank',
  'fas fa-wallet',
  'fas fa-credit-card',
  'fas fa-coins',
  'fas fa-gift',
  'fas fa-shopping-cart',
  'fas fa-shuttle-van',
  'fas fa-tshirt',
  'fas fa-bell',
  'fab fa-btc',
  'fas fa-percent',
  'fas fa-archive',
  'fas fa-plane',
  'fas fa-globe',
  'fas fa-birthday-cake',
  'fas fa-wifi',
  'fas fa-server',
  'fas fa-utensils',
  'fas fa-glass-cheers',
  'fas fa-hands-helping',
  'fas fa-calendar-alt',
  'fas fa-certificate',
  'fas fa-ellipsis-h'
]

export default {
  icons,
  props: {
    value: {
      type: String
    },
    color: {
      type: String
    }
  },
  data () {
    return {
      open: false
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
  margin: 0 !important;

  &:hover,
  &.selected {
    background-color: var(--alert-background-color);
    cursor: pointer;
  }
}
</style>

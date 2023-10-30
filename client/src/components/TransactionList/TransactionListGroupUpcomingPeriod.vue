<template>
  <DropdownWaFloating>
    <template #toggler>
      <button class="circle light-gray">
        <span class="icon"><i class="fas fa-ellipsis-v" /></span>
      </button>
    </template>
    <ul class="menu">
      <li>
        <a @click.prevent="setFeaturePeriod(7)"><span>{{ $t("nextDays", { count: 7 }) }}</span></a>
      </li>
      <li>
        <a @click.prevent="setFeaturePeriod(30)"><span>{{ $t("nextDays", { count: 30 }) }}</span></a>
      </li>
      <li>
        <a @click.prevent="setFeaturePeriod(90)"><span>{{ $t("nextDays", { count: 90 }) }}</span></a>
      </li>
      <li class="bordered-top">
        <a @click.prevent="listCompactMode = !listCompactMode">
          <span
            v-show="!listCompactMode"
            class="icon"
          >
            <i class="fas fa-ellipsis-h" />
          </span>
          <span
            v-show="listCompactMode"
            class="icon"
          >
            <i class="fas fa-list" />
          </span>
          <span>{{ listCompactMode ? $t('listView') : $t('compactView') }}</span>
        </a>
      </li>
    </ul>
  </DropdownWaFloating>
</template>

<script setup>
import DropdownWaFloating from '../Inputs/DropdownWaFloating.vue'
import { useStorage } from '@vueuse/core'

const listCompactMode = useStorage('list_compact_mode', true)
</script>

<script>
export default {
  props: {
    upcomingBlockOpened: {
      type: Boolean
    }
  },

  computed: {
    featurePeriod: {
      get () {
        return this.$store.state.transaction.featurePeriod
      },

      set (val) {
        this.$store.commit('transaction/setFeaturePeriod', val)
      }
    }
  },

  created () {
    this.featurePeriod =
      +localStorage.getItem('upcoming_transactions_days') || this.featurePeriod
  },

  methods: {
    setFeaturePeriod (days) {
      this.featurePeriod = days
      localStorage.setItem('upcoming_transactions_days', days)
      this.$emit('updateUpcomingBlockOpened', true)
    }
  }
}
</script>

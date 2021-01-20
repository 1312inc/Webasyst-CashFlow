<template>
  <div v-if="$helper.isDesktopEnv" sticky-container class="c-chart-pie-sticky-container">
    <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
      <transition name="fade">
        <AmChartPieSticky
          v-if="$store.state.transaction.defaultGroupTransactions"
        />
      </transition>
    </div>
  </div>
</template>

<script>
import AmChartPieSticky from '@/components/AmChartPieSticky'
export default {
  components: {
    AmChartPieSticky
  }
}
</script>

<style lang="scss">
  @media screen and (max-width: 980px) {
    .c-chart-pie-sticky-container {
      display: none;
    }
  }
</style>

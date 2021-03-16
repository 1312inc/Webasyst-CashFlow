<template>
  <div
    v-if="$helper.isDesktopEnv"
    sticky-container
    class="c-chart-pie-sticky-container"
  >
    <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
      <AmChartPieSticky
        v-if="this.$store.state.transaction.transactions.data.length"
      />
    </div>
  </div>
</template>

<script>
import AmChartPieSticky from './AmChartPieSticky'
export default {
  components: {
    AmChartPieSticky
  }
}
</script>

<style lang="scss">
.c-chart-pie-sticky-container {
  width: 35%;
  min-width: 340px;
  padding-left: 1rem;
}
@media screen and (max-width: 980px) {
  .c-chart-pie-sticky-container {
    display: none;
  }
}
</style>

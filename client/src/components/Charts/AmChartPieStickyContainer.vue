<template>
  <div
    v-if="$helper.isDesktopEnv"
    sticky-container
    class="c-chart-pie-sticky-container"
  >
    <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
      <div class="c-chart-pie-sticky-container__inner">
        <AmChartPieSticky
          v-if="this.$store.state.transaction.transactions.data.length"
        />
      </div>
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
  min-width: 340px;
  padding-left: 1rem;

  &__inner {
    max-width: 340px;
    margin: 0 auto;
  }
}
@media screen and (max-width: 980px) {
  .c-chart-pie-sticky-container {
    display: none;
  }
}
</style>

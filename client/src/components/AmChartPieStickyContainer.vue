<template>
  <div sticky-container>
    <div v-sticky sticky-offset="{top: 80}" sticky-z-index="12">
      <transition name="fade">
        <AmChartPieSticky
          v-if="$store.state.transaction.defaultGroupTransactions.length"
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

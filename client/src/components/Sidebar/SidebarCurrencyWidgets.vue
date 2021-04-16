<template>
  <div v-if="balanceFlow.length > 0" class="custom-mt-24">
    <div class="heading">
      {{ $t("Next 90 days") }}
    </div>
    <div class="bricks">
      <SidebarCurrencyWidgetsItems
        v-for="item in balanceFlow"
        :key="item.currency"
        :balanceFlow="item"
      />
    </div>
  </div>
</template>

<script>
import SidebarCurrencyWidgetsItems from './SidebarCurrencyWidgetsItems'
export default {
  components: {
    SidebarCurrencyWidgetsItems
  },

  computed: {
    balanceFlow () {
      return this.$store.state.balanceFlow.balanceFlow
    }
  },

  mounted () {
    this.$store.dispatch('balanceFlow/getBalanceFlow')
  }
}
</script>

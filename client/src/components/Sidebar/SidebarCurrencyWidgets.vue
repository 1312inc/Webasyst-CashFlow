<template>
  <div v-if="balanceFlow.length > 0" class="custom-mt-24">
    <div class="custom-mx-16">
      <div class="heading custom-mx-0">
        {{ $t("Next 90 days") }}
      </div>
    </div>
    <ul class="menu">
      <SidebarCurrencyWidgetsItems
        v-for="currency in balanceFlow"
        :key="currency.currency"
        :balanceFlow="currency"
      />
    </ul>
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

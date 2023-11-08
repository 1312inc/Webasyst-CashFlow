<template>
  <router-link
    to="/transactions"
    :class="{ selected: $route.name === 'Transactions' }"
    class="brick"
  >
    <span
      v-if="count > 0"
      :class="{ badge: todayCount.onbadge > 0 }"
      class="count"
    >{{ count }}</span>
    <span
      class="icon"
    ><i class="fas fa-list" /></span>
    {{ $t("transactions") }}
  </router-link>
</template>

<script>
export default {
  computed: {
    todayCount () {
      return this.$store.state.transaction.todayCount
    },
    count () {
      return this.todayCount.onbadge > 0
        ? this.todayCount.onbadge
        : this.todayCount.today
    }
  },

  mounted () {
    this.$store.dispatch('transaction/getTodayCount')
  }
}
</script>

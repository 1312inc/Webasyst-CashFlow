<template>
  <router-link
    :to="{ name: 'Transactions' }"
    :class="{ selected: $route.name === 'Transactions' }"
    class="brick custom-mb-0"
  >
    <span
      v-if="count > 0"
      :class="{ badge: todayCount.onbadge > 0 }"
      class="count"
      >{{ count }}</span
    >
    <span
      class="icon"
      :style="{
        color: (todayCount.onbadge > 0 || todayCount.today > 0) && 'gold'
      }"
      ><i class="fas fa-list"></i
    ></span>
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

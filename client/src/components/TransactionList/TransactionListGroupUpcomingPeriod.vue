<template>
  <div
    @mouseover="$refs.dropdown.style.display = 'block'"
    @mouseleave="$refs.dropdown.style.display = 'none'"
    class="dropdown z-100"
  >
    <a href="#" class="button light-gray"
      ><span class="icon"><i class="fas fa-ellipsis-v"></i></span
    ></a>
    <div class="dropdown-body" ref="dropdown">
      <ul class="menu">
        <li>
          <a href="#" @click.prevent="setFeaturePeriod(1)"
            ><span>{{ $t("tomorrow") }}</span></a
          >
        </li>
        <li>
          <a href="#" @click.prevent="setFeaturePeriod(7)"
            ><span>{{ $t("nextDays", { count: 7 }) }}</span></a
          >
        </li>
        <li>
          <a href="#" @click.prevent="setFeaturePeriod(30)"
            ><span>{{ $t("nextDays", { count: 30 }) }}</span></a
          >
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    featurePeriod: {
      get () {
        return this.$store.state.transaction.featurePeriod
      },

      set (val) {
        this.$store.commit('transaction/setFeaturePeriod', val)
      }
    },

    upcomingBlockOpened: {
      get () {
        return this.$store.state.transaction.upcomingBlockOpened
      },

      set (val) {
        this.$store.commit('transaction/setUpcomingBlockOpened', val)
      }
    }
  },

  created () {
    this.featurePeriod =
      +localStorage.getItem('upcoming_transactions_days') || this.featurePeriod
    this.upcomingBlockOpened =
      +localStorage.getItem('upcoming_transactions_show') === 0
        ? 0
        : this.upcomingBlockOpened
  },

  methods: {
    setFeaturePeriod (days) {
      this.featurePeriod = days
      this.upcomingBlockOpened = 1
      localStorage.setItem('upcoming_transactions_days', days)
      localStorage.setItem('upcoming_transactions_show', 1)
    }
  }
}
</script>

<style scoped>
.button {
  border-radius: 50%;
  padding: 0.75em;
  line-height: 0;
}
</style>

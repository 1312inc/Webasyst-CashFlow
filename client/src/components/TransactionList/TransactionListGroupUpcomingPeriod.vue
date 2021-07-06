<template>
  <div
    @mouseover="$refs.dropdown.style.display = 'block'"
    @mouseleave="$refs.dropdown.style.display = 'none'"
    class="dropdown z-100"
  >
    <button class="button light-gray"
      ><span class="icon"><i class="fas fa-ellipsis-v"></i></span
    ></button>
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

<style scoped>
.button {
  border-radius: 50%;
  padding: 0.75em;
  line-height: 0;
}
</style>

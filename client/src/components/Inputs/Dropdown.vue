<template>
  <div class="wa-select solid">
    <select
      v-model="value"
      class="c-select"
    >
      <option
        v-for="item in items"
        :key="item.key"
        :value="item.value"
      >
        {{ $t(item.key) }}
      </option>
    </select>
  </div>
</template>

<script>
import { intervals } from '@/utils/getDateFromLocalStorage'
export default {
  props: {
    type: {
      type: String,
      required: true
    }
  },

  computed: {
    items () {
      return intervals[this.type]
    },

    value: {
      get () {
        return this.$store.state.transaction.chartInterval[this.type]
      },
      set (value) {
        localStorage.setItem(
          `interval_${this.type}`,
          this.getNameByValue(value)
        )
        if (this.$store.state.transaction.detailsInterval.from || this.$store.state.transaction.detailsInterval.to) {
          this.$store.dispatch('transaction/updateDetailsInterval', { from: '', to: '' })
        }

        this.$store.commit('transaction/updateChartInterval', {
          [this.type]: value
        })
      }
    }
  },

  methods: {
    getNameByValue (value) {
      return this.items.find(e => e.value === value).key
    }
  }
}
</script>

<style lang="scss">
.c-select {
  @include for-mobile {
    font-size: 0.86rem !important;
    padding-right: 1.8rem !important;
  }
}
</style>

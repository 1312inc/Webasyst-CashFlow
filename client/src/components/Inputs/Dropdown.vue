<template>
  <div class="wa-select solid">
    <select v-model="value">
      <option v-for="item in items" :key="item.key" :value="item.value">
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

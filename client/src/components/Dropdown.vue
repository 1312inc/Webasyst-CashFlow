<template>
  <div class="wa-select">
    <select v-model="value">
      <option v-for="(item, i) in items" :key="i" :value="item.value">
        {{ item.title }}
      </option>
    </select>
  </div>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      required: true
    }
  },

  computed: {
    items () {
      return this.$store.state.intervals[this.type]
    },

    value: {
      get () {
        return this.$store.state.transaction.queryParams[this.type]
      },
      set (value) {
        localStorage.setItem(`interval_${this.type}`, this.getNameByValue(value))
        this.$store.commit('transaction/updateQueryParams', {
          [this.type]: value
        })
      }
    }
  },

  methods: {
    getNameByValue (value) {
      return this.items.find(e => e.value === value).title
    }
  }
}
</script>

<template>
  <div
    :class="{
      'tw-text-green-500': type === 'income',
      'tw-text-red-500': type === 'expense',
    }"
  >
    <div>
      <i
        class="fas"
        :class="{
          'fa-caret-up': type === 'income',
          'fa-caret-down': type === 'expense',
        }"
      ></i
      >&nbsp;<span class="small">{{ $numeral(total).format() }}</span
      >&nbsp;<span v-if="type === 'expense'" class="small hint"
        >({{ date }})</span
      >
    </div>
  </div>
</template>

<script>
export default {
  props: {
    group: {
      type: Array,
      required: true
    },

    type: {
      type: String,
      required: true
    }
  },

  computed: {
    total () {
      return this.group
        .filter(e => (this.type === 'income' ? e.amount >= 0 : e.amount < 0))
        .reduce((acc, e) => {
          return acc + e.amount
        }, 0)
    },

    date () {
      if (this.group.length > 1) {
        return `${this.$moment(this.group[this.group.length - 1].date).format('ll')} 
        – ${this.$moment(this.group[0].date).format('ll')}`
      } else {
        return this.$moment(this.group[0].date).format('ll')
      }
    }
  }
}
</script>

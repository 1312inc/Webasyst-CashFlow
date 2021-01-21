<template>
  <div
    :class="{
      'text-green': type === 'income',
      'text-red': type === 'expense',
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
      >&nbsp;<span class="small">{{ $numeral(total).format('0,0[.]00') }}</span>
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
    }
  }
}
</script>

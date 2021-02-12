<template>
  <div class="flexbox space-12">
    <div
      v-for="(currency, i) in currencies"
      :key="i"
      :class="{
        'text-green': type === 'income',
        'text-red': type === 'expense',
      }"
    >
      <div>
        <span class="small semibold">{{
          $helper.toCurrency({
            value: getTotalByCurrency(currency),
            currencyCode: currency,
            isReverse: type === 'expense',
            prefix: type === "income" ? "+ " : "− ",
          })
        }}</span>
      </div>
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
    currencies () {
      return this.group.reduce((acc, e) => {
        const currency = this.$store.getters['account/getById'](e.account_id).currency
        if (currency && !acc.includes(currency)) {
          acc.push(currency)
        }
        return acc
      }, [])
    }
  },

  methods: {
    getTotalByCurrency (currency) {
      return this.group
        .filter(
          e =>
            (this.type === 'income' ? e.amount >= 0 : e.amount < 0) &&
            this.$store.getters['account/getById'](e.account_id).currency === currency
        )
        .reduce((acc, e) => {
          return acc + e.amount
        }, 0)
    }
  }
}
</script>

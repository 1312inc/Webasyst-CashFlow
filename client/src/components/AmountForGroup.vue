<template>
  <div v-if="currencies.length" class="flexbox space-12">
    <div
      v-for="currency in currencies"
      :key="currency"
      :class="{
        'text-green': type === 'income',
        'text-red': type === 'expense',
        'text-blue': type === 'profit',
      }"
    >
      <i v-if="type === 'profit'" class="fas fa-sign-out-alt text-blue"></i>
      <span class="small semibold">{{
        $helper.toCurrency({
          value: getTotalByCurrency(currency),
          currencyCode: currency,
          isAbs: true,
          prefix: type === "income" ? "+ " : type === "expense" ? "− " : " ",
        })
      }}</span>
    </div>
  </div>
  <div
    v-else
    :class="{
      'text-green': type === 'income',
      'text-red': type === 'expense',
      'text-blue': type === 'profit',
    }"
  >
    <i v-if="type === 'profit'" class="fas fa-sign-out-alt text-blue"></i>
    <span class="small semibold"
      >{{ type === "income" ? "+ " : type === "expense" ? "− " : " " }}0</span
    >
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
        const currency = this.$store.getters['account/getById'](e.account_id)
          .currency
        if (!acc.includes(currency)) {
          acc.push(currency)
        }
        return acc
      }, [])
    },

    categoriesIDs () {
      const categories = this.$store.state.category.categories.filter(c => {
        if (this.type === 'profit') {
          return c.is_profit === true
        }
        return c.type === this.type && c.is_profit === false
      })
      return categories.map(c => c.id)
    }
  },

  methods: {
    getTotalByCurrency (currency) {
      return this.group
        .filter(
          e =>
            this.categoriesIDs.includes(e.category_id) &&
            this.$store.getters['account/getById'](e.account_id).currency ===
              currency
        )
        .reduce((acc, e) => {
          return acc + e.amount
        }, 0)
    }
  }
}
</script>

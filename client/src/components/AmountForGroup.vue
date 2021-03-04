<template>
  <div v-if="currencies.length" class="flexbox space-12">
    <div
      v-for="currency in currencies"
      :key="currency"
      :class="{
        'text-green': type === 'income',
        'text-red': type === 'expense',
        'text-blue': profit,
      }"
    >
      <i v-if="profit" class="fas fa-piggy-bank text-blue"></i>
      <span class="small semibold">{{
        $helper.toCurrency({
          value: getTotalByCurrency(currency),
          currencyCode: currency,
          isAbs: true,
          prefix: profit ? " " : type === "income" ? "+ " : "− ",
        })
      }}</span>
    </div>
  </div>
  <div
    v-else
    :class="{
      'text-green': type === 'income',
      'text-red': type === 'expense',
      'text-blue': profit,
    }"
  >
    <i v-if="profit" class="fas fa-piggy-bank text-blue"></i>
    <span class="small semibold"
      >{{ profit ? " " : type === "income" ? "+ " : "− " }}0</span
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
    },
    profit: {
      type: Boolean,
      default: false
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
      let categories = this.$store.state.category.categories.filter(
        c => c.type === this.type
      )
      if (this.type === 'expense') {
        categories = categories.filter(c => c.is_profit === this.profit)
      }
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

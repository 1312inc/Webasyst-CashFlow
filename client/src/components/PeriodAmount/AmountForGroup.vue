<template>
  <div class="flexbox justify-end middle wrap space-12">
    <div v-for="(type, i) in $_amountMixin_amountTypes" :key="i">
      <template v-if="currencies.length">
        <div
          v-for="currency in currencies"
          :key="currency"
          :class="{
            'text-green': type === 'income',
            'text-red': type === 'expense',
            'text-blue': type === 'profit'
          }"
          :title="
            type === 'profit'
              ? $t('profit')
              : type === 'income'
              ? $t('income')
              : $t('expense')
          "
        >
          <i v-if="type === 'profit'" class="fas fa-coins text-blue small"></i>
          <span class="small semibold">{{
            $helper.toCurrency({
              value: getTotalByCurrency(currency, type),
              currencyCode: currency,
              isAbs: true,
              prefix: type === "income" ? "+ " : type === "expense" ? "− " : " "
            })
          }}</span>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import amountMixin from '@/mixins/amountMixin'
export default {
  props: {
    group: {
      type: Array,
      required: true
    }
  },

  mixins: [amountMixin],

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
    }
  },

  methods: {
    categoriesByType (type) {
      return this.$store.state.category.categories.filter(c =>
        type === 'profit'
          ? c.is_profit === true
          : c.type === type && c.is_profit === false
      ).map(c => c.id)
    },

    getTotalByCurrency (currency, type) {
      return this.group
        .filter(
          t =>
            this.categoriesByType(type).includes(t.category_id) &&
            this.$store.getters['account/getById'](t.account_id).currency ===
              currency
        )
        .reduce((acc, e) => acc + e.amount, 0)
    }
  }
}
</script>

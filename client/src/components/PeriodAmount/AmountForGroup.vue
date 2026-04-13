<template>
  <div
    v-if="currencies.length"
    class="flexbox justify-end middle wrap space-12"
    :class="{'is-shown': isShown}"
  >
    <div class="flexbox space-12">
      <div class="c-group-amount">
        <div
          v-for="currency in currencies"
          :key="currency"
          class="flexbox middle wrap space-12"
        >
          <div
            v-for="(type, i) in $_amountMixin_amountTypes"
            :key="i"
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
            <i
              v-if="type === 'profit'"
              class="fas fa-coins text-blue small"
            />
            <span class="small semibold">{{
              $helper.toCurrency({
                value: getTotalByCurrency(currency, type),
                currencyCode: currency,
                isAbs: true,
                prefix: type === "income" ? "+ " : type === "expense" ? "− " : " "
              })
            }}</span>
          </div>
          <div
            v-if="$_amountMixin_amountTypes.includes('profit')"
            :title="$t('delta')"
          >
            <span class="small semibold gray">
              {{
                $helper.toCurrency({
                  value: amountDelta(currency),
                  currencyCode: currency,
                  isDynamics: true,
                  prefix: "&#916;&nbsp;"
                })
              }}
            </span>
          </div>
        </div>
      </div>
      <a
        href="#"
        class="button circle light-gray mobile-only"
        style="flex: none;"
        @click.prevent="isShown = !isShown"
      >
        <span v-show="!isShown">
          <i class="fas fa-chevron-down" />
        </span>
        <span v-show="isShown">
          <i class="fas fa-chevron-up" />
        </span>
      </a>
    </div>
  </div>
</template>

<script>
import amountMixin from '@/mixins/amountMixin'
export default {

  mixins: [amountMixin],
  props: {
    group: {
      type: Array,
      required: true
    }
  },

  data () {
    return {
      isShown: false
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
    }
  },

  methods: {
    categoriesByType (type) {
      return this.$store.state.category.categories
        .filter(c =>
          type === 'profit'
            ? !!c.is_profit
            : c.type === type && !c.is_profit
        )
        .map(c => c.id)
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
    },

    amountDelta (currency) {
      return (
        Math.abs(this.getTotalByCurrency(currency, 'income')) -
        Math.abs(this.getTotalByCurrency(currency, 'expense')) -
        Math.abs(this.getTotalByCurrency(currency, 'profit'))
      )
    }
  }
}
</script>

<style>
@media screen and (max-width: 760px) {
  .c-group-amount {
    display: none;
  }

  .is-shown .c-group-amount {
    display: block;
  }
}
</style>

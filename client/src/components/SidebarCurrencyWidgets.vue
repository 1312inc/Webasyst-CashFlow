<template>
  <div class="custom-mt-24">
    <div v-if="balanceFlow.length > 1" class="tw-mx-4">
      <div class="heading custom-mx-0">
        {{ this.$moment().format('LL') }} <span class="count">+30d</span>
      </div>
    </div>
    <ul class="menu">
      <li v-for="currency in balanceFlow" :key="currency.currency">
        <router-link
          :to="`/currency/${currency.currency}`"
          style="display: block"
        >
          <div class="flexbox middle">
            <div class="wide bold nowrap">
              {{
                $helper.toCurrency(
                  currency.balances.now.amount,
                  currency.currency
                )
              }}
            </div>
            <div
              class="nowrap small"
              v-html="
                `${
                  currency.balances.to.amountShorten
                }&nbsp;${$helper.currencySignByCode(currency.currency)}`
              "
            ></div>
            <div class="custom-ml-4">
              <span
                class="c-bwc-badge small nowrap"
                :class="
                  currency.balances.diff.amount >= 0
                    ? 'c-bwc-badge--green'
                    : 'c-bwc-badge--red'
                "
                v-html="currency.balances.diff.amountShorten"
              ></span>
            </div>
          </div>
          <CurrencyChart :currency="currency" />
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import CurrencyChart from '@/components/CurrencyChart'
export default {
  components: {
    CurrencyChart
  },

  computed: {
    ...mapState('transaction', ['balanceFlow'])
  },

  created () {
    this.getBalanceFlow()

    this.unsubscribeFromActions = this.$store.subscribeAction({
      after: action => {
        if (
          (action.type === 'transaction/update' ||
            action.type === 'transaction/delete' ||
            action.type === 'transactionBulk/bulkDelete' ||
            action.type === 'transactionBulk/bulkMove' ||
            action.type === 'account/update' ||
            action.type === 'account/delete' ||
            action.type === 'category/delete') &&
          !action.payload.silent
        ) {
          this.getBalanceFlow()
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromActions()
  },

  methods: {
    getBalanceFlow () {
      this.$store.dispatch('transaction/getBalanceFlow')
    }
  }
}
</script>

<style lang="scss">
.c-bwc-badge {
  color: #fff;
  padding: 2px 6px;
  border-radius: 4px;

  &--green {
    background: #3ec55e;
  }

  &--red {
    background: #fc3d38;
  }
}
</style>

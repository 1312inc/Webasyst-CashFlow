<template>
  <div class="custom-mt-24">
    <ul class="menu">
      <li v-for="currency in balanceFlow" :key="currency.currency">
        <router-link
          :to="`/currency/${currency.currency}`"
          style="display: block"
        >
          <div class="flexbox middle">
            <div class="wide large bold nowrap">
              {{
                $helper.toCurrency({
                  value: currency.balances.now.amount,
                  currencyCode: currency.currency,
                })
              }}
            </div>
            <div class="custom-ml-4">
              <span
                class="c-bwc-badge small nowrap"
                :class="
                  currency.balances.diff.amount >= 0
                    ? 'c-bwc-badge--green'
                    : 'c-bwc-badge--red'
                "
                v-html="
                  `${
                    currency.balances.diff.amount > 0
                      ? '+ '
                      : currency.balances.diff.amount < 0
                      ? '− '
                      : ''
                  }${currency.balances.diff.amountShorten}`
                "
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
    ...mapState('balanceFlow', ['balanceFlow'])
  },

  mounted () {
    this.$store.dispatch('balanceFlow/getBalanceFlow')
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

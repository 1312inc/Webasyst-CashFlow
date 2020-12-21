<template>
  <div class="custom-mt-24">
    <div v-if="balanceFlow.length > 1" class="tw-mx-4">
      <h5>{{ $t("cashOnHand") }}</h5>
    </div>
    <ul class="menu">
      <li v-for="currency in balanceFlow" :key="currency.currency">
        <router-link
          :to="`/currency/${currency.currency}`"
          class="flexbox middle full-width"
        >
          <div class="wide">
            {{ balanceFlow.length > 1 ? currency.currency : $t("cashOnHand") }}
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

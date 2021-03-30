<template>
  <div
    @click="navigateTo(`/currency/${balanceFlow.currency}`)"
    class="brick full-width"
  >
    <div class="flexbox middle">
      <div
        :class="{ 'text-red': balanceFlow.balances.now.amount < 0 }"
        class="wide black bold nowrap c-bwc-balance"
      >
        {{
          $helper.toCurrency({
            value: balanceFlow.balances.now.amount,
            currencyCode: balanceFlow.currency,
          })
        }}
      </div>
      <div v-if="alertDate" class="custom-ml-4">
        <span
          :title="title"
          :class="
            alertDate.amount >= 0 ? 'c-bwc-badge--green' : 'c-bwc-badge--red'
          "
          class="c-bwc-badge small nowrap"
          ><i
            :class="
              alertDate.amount >= 0
                ? 'fa-arrow-circle-up'
                : 'fa-exclamation-triangle'
            "
            class="fas custom-mr-4"
            style="color: white"
          ></i
          >{{ date }}</span
        >
      </div>
    </div>
    <CurrencyChart :currency="balanceFlow" />
  </div>
</template>

<script>
import CurrencyChart from '@/components/Charts/CurrencyChart'
export default {
  props: ['balanceFlow'],

  components: {
    CurrencyChart
  },

  computed: {
    alertDate () {
      const now = this.balanceFlow.balances.now.date
      const nowAmount = this.balanceFlow.balances.now.amount
      return this.balanceFlow.data.find(element => {
        return (
          element.period > now &&
          nowAmount !== 0 &&
          Math.sign(nowAmount) !== Math.sign(element.amount)
        )
      })
    },
    date () {
      return this.$moment(this.alertDate.period).format('MMM, D')
    },
    title () {
      return (
        (this.alertDate.amount >= 0
          ? this.$t('arrow.up')
          : this.$t('arrow.down')) +
        ' ' +
        this.date
      )
    }
  },

  methods: {
    navigateTo (path) {
      if (this.$route.path !== path) {
        this.$router.push(path)
      }
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
    background: var(--green);
  }

  &--red {
    background: var(--red);
  }
}
.c-bwc-balance {
    font-size: 1rem;
}
</style>

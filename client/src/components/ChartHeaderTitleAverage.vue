<template>
  <div
    v-if="average !== 0 && !$store.state.transaction.loadingChart"
    :class="average >= 0 ? 'text-green' : 'text-red'"
    :title="$t('forecasted')"
    class="opacity-50 nowrap custom-mt-4 semibold small"
  >
    <i class="fas fa-tachometer-alt" />
    {{
      $helper.toCurrency({
        value: average,
        currencyCode: currencyCode,
        isDynamics: true
      })
    }}/{{ $t("shortMonth") }}
  </div>
</template>

<script>
import amountMixin from '@/mixins/amountMixin'
export default {
  mixins: [amountMixin],
  props: {
    currencyCode: {
      type: String
    }
  },
  computed: {
    average () {
      const amount = this.$_amountMixin_amountDelta('to')
      const months = Math.ceil(
        Math.abs(
          this.$moment().diff(
            this.$moment(this.$store.state.transaction.chartInterval.to),
            'months',
            true
          )
        )
      )
      return amount / months
    }
  }
}
</script>

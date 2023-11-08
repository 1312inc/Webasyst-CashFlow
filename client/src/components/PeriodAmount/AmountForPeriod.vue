<template>
  <div v-if="!loadingChart">
    <div
      v-for="(type, i) in $_amountMixin_amountTypes"
      :key="i"
    >
      <div
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
        <div class="custom-ml-12">
          <i
            v-if="type === 'profit'"
            class="fas fa-coins text-blue smaller"
          />
          <span class="smaller semibold">{{
            $helper.toCurrency({
              value: $_amountMixin_getTotalByType(type, period),
              currencyCode: currencyCode,
              prefix: type === "income" ? "+ " : type === "expense" ? "− " : " "
            })
          }}</span>
        </div>
      </div>
    </div>
    <div>
      <div
        v-if="$_amountMixin_amountTypes.includes('profit')"
        :title="$t('delta')"
      >
        <div class="custom-ml-12">
          <span class="smaller semibold gray">
            {{
              $helper.toCurrency({
                value: $_amountMixin_amountDelta(period),
                currencyCode: currencyCode,
                isDynamics: true,
                prefix: "&#916;&nbsp;"
              })
            }}
          </span>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <div
      v-for="i in $_amountMixin_amountTypes.length + 1"
      :key="i"
      class="skeleton"
    >
      <span class="skeleton-line custom-mb-4" />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import amountMixin from '@/mixins/amountMixin'
export default {

  mixins: [amountMixin],
  props: {
    period: {
      type: String,
      required: true
    }
  },

  computed: {
    ...mapState('transaction', [
      'chartData',
      'chartDataCurrencyIndex',
      'loadingChart'
    ]),

    currencyCode () {
      return this.chartData[this.chartDataCurrencyIndex]?.currency
    }
  }
}
</script>

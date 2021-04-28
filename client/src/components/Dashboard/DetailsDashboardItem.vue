<template>
  <div class="custom-mb-16">
    <div v-if="showIncome" class="custom-mb-16">
      <div class="flexbox wrap middle full-width">
        <div class="large bold">
          {{ $t("income") }}
        </div>
        <div class="large bold text-green mobile-only nowrap">
          {{
            $helper.toCurrency({
              value: itemData.income.totalAmount,
              currencyCode: itemData.currency,
              isDynamics: true,
            })
          }}
        </div>
      </div>
      <div class="flexbox middle">
        <ChartBar
          :data="itemData.income.data"
          :width="(itemData.income.totalAmount / maxAmount) * 100"
        />
        <div
          class="desktop-and-tablet-only custom-ml-12 align-center larger text-green bold nowrap"
        >
          {{
            $helper.toCurrency({
              value: itemData.income.totalAmount,
              currencyCode: itemData.currency,
              isDynamics: true,
            })
          }}
        </div>
      </div>
      <AmChartLegend
        :legendItems="itemData.income.data"
        :currencyCode="itemData.currency"
        :isColumnStyle="false"
      />
    </div>

    <div v-if="showExpense" class="custom-mb-16">
      <div class="flexbox wrap middle full-width">
        <div class="large bold">
          {{ $t("expense") }}
        </div>
        <div class="large bold text-red mobile-only nowrap">
          {{
            $helper.toCurrency({
              value: itemData.expense.totalAmount,
              currencyCode: itemData.currency,
              isDynamics: true,
              isReverse: true,
            })
          }}
        </div>
      </div>
      <div class="flexbox middle">
        <ChartBar
          :data="itemData.expense.data"
          :width="(itemData.expense.totalAmount / maxAmount) * 100"
        />
        <div
          class="desktop-and-tablet-only custom-ml-12 align-center larger text-red bold nowrap"
        >
          {{
            $helper.toCurrency({
              value: itemData.expense.totalAmount,
              currencyCode: itemData.currency,
              isDynamics: true,
              isReverse: true,
            })
          }}
        </div>
      </div>
      <AmChartLegend
        :legendItems="itemData.expense.data"
        :currencyCode="itemData.currency"
        :isReverse="true"
        :isColumnStyle="false"
      />
    </div>

    <div v-if="showProfit">
      <div class="flexbox wrap middle full-width">
        <div class="large bold">
          {{ $t("profit") }}
        </div>
        <div class="large bold text-blue mobile-only nowrap">
          {{
            $helper.toCurrency({
              value: itemData.profit.totalAmount,
              currencyCode: itemData.currency,
            })
          }}
        </div>
      </div>
      <div class="flexbox middle">
        <ChartBar
          :data="itemData.profit.data"
          :width="(itemData.profit.totalAmount / maxAmount) * 100"
        />
        <div
          class="desktop-and-tablet-only custom-ml-12 align-center larger text-blue bold nowrap"
        >
          {{
            $helper.toCurrency({
              value: itemData.profit.totalAmount,
              currencyCode: itemData.currency,
            })
          }}
        </div>
      </div>
      <AmChartLegend
        :legendItems="itemData.profit.data"
        :currencyCode="itemData.currency"
        :isColumnStyle="false"
      />
    </div>
  </div>
</template>

<script>
import ChartBar from '@/components/Charts/AmChartBar'
import AmChartLegend from '@/components/Charts/AmChartLegend'
export default {
  props: ['itemData'],

  components: {
    ChartBar,
    AmChartLegend
  },

  computed: {
    currentEntityType () {
      return this.$store.getters.getCurrentType.type
    },

    showIncome () {
      return this.currentEntityType
        ? this.currentEntityType && this.currentEntityType === 'income'
        : true
    },

    showExpense () {
      return this.currentEntityType
        ? this.currentEntityType &&
            this.currentEntityType === 'expense' &&
            !this.$store.getters.getCurrentType.is_profit
        : true
    },

    showProfit () {
      return this.currentEntityType
        ? this.currentEntityType &&
            this.currentEntityType === 'expense' &&
            this.$store.getters.getCurrentType.is_profit
        : true
    },

    maxAmount () {
      return Math.max(
        this.itemData.income.totalAmount,
        this.itemData.expense.totalAmount,
        this.itemData.profit.totalAmount
      ) || 1
    }
  }
}
</script>

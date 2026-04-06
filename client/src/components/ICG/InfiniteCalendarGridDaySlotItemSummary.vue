<script setup>

const props = defineProps({
  summary: {
    type: Object,
    default: () => {}
  }
})

</script>

<template>
  <div
    class="custom-p-8 align-left"
    style="display: flex; flex-direction: column; gap: .2rem;"
  >
    <div
      v-for="cur in props.summary.data"
      :key="cur.currency"
    >
      <div
        v-if="cur.amountIncome"
        class="text-green"
        :title="$t('income')"
      >
        {{
          $helper.toCurrency({
            value: cur.amountIncome,
            currencyCode: cur.currency,
            prefix: '+'
          })
        }}
        <span
          v-if="cur.countIncome > 1"
          class="badge light-gray small"
        >
          {{ cur.countIncome }}
        </span>
      </div>
      <div
        v-if="cur.amountExpense"
        class="text-red"
        :title="$t('expense')"
      >
        {{
          $helper.toCurrency({
            value: cur.amountExpense,
            currencyCode: cur.currency,
            prefix: '-'
          })
        }}
        <span
          v-if="cur.countExpense > 1"
          class="badge light-gray small"
        >
          {{ cur.countExpense }}
        </span>
      </div>
      <div
        v-if="cur.amountProfit"
        class="text-blue"
        :title="$t('profit')"
      >
        <i class="fas fa-coins text-blue smaller" /> {{
          $helper.toCurrency({
            value: cur.amountProfit,
            currencyCode: cur.currency,
          })
        }}
        <span
          v-if="cur.countProfit > 1"
          class="badge light-gray small"
        >
          {{ cur.countProfit }}
        </span>
      </div>
    </div>
  </div>
</template>

import { reactive } from 'vue'

export const breakdownTotals = reactive({
  from: null,
  to: null
})

export function setBreakdownTotals (mode, item) {
  if (mode !== 'from' && mode !== 'to') return
  breakdownTotals[mode] = item
    ? {
        income: item.income.totalAmount,
        expense: item.expense.totalAmount,
        profit: item.profit.totalAmount
      }
    : null
}

export function clearBreakdownTotals (mode) {
  if (mode !== 'from' && mode !== 'to') return
  breakdownTotals[mode] = null
}

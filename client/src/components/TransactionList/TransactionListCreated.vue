<template>
  <div class="custom-mb-24 c-transaction-section">
    <div class="flexbox middle custom-py-8">
      <div class="flexbox middle space-12 wide">
        <div v-if="$helper.showMultiSelect()" style="min-width: 1rem"></div>
        <h3 class="c-transaction-section__header">Just Created</h3>
      </div>
    </div>
    <transition-group name="list" tag="ul" class="c-list list">
      <TransactionListGroupRow
        v-for="transaction in $store.state.transaction.createdTransactions"
        :key="transaction.id"
        :transaction="transaction"
        :showChecker="isShowChecker"
      />
    </transition-group>
  </div>
</template>

<script>
import TransactionListGroupRow from './TransactionListGroupRow/TransactionListGroupRow'
export default {
  components: {
    TransactionListGroupRow
  },

  computed: {
    isShowChecker () {
      return this.$store.state.transaction.createdTransactions.some(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    }
  },

  beforeDestroy () {
    // Clear created transactions temp list
    this.$store.commit('transaction/deleteCreatedTransaction', [])
  }
}
</script>

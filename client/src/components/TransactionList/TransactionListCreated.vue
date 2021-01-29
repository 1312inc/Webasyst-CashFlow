<template>
  <div class="custom-mb-24 c-transaction-section">
    <div class="flexbox flexbox-mobile middle custom-py-8">
      <div class="flexbox middle space-12 wide">
        <div v-if="$helper.showMultiSelect()" style="width: 1rem"></div>
        <h3 class="c-transaction-section__header">Just Created</h3>
      </div>
    </div>
    <transition-group name="list" tag="ul" class="c-list list">
      <TransactionListGroupRow
        v-for="transaction in $store.state.transaction.createdTransactions"
        :key="transaction.id"
        :transaction="transaction"
      />
    </transition-group>
  </div>
</template>

<script>
import TransactionListGroupRow from '@/components/TransactionList/TransactionListGroupRow'
export default {
  components: {
    TransactionListGroupRow
  },

  beforeDestroy () {
    // Clear created transactions temp list
    this.$store.commit('transaction/setCreatedTransactions', [])
  }
}
</script>

<template>
  <div
    v-if="transactionsJustCreated.length"
    class="custom-mb-16 c-transaction-section"
  >
    <div class="flexbox middle custom-py-8">
      <div class="flexbox middle space-12 wide">
        <div
          v-if="$helper.showMultiSelect()"
          style="min-width: 1rem"
        />
        <h4 class="c-transaction-section__header custom-ml-8">
          {{ $t("justCreated") }}
        </h4>
      </div>
    </div>
    <ul class="list c-list">
      <TransactionListGroupRow
        v-for="transaction in transactionsJustCreated"
        :key="transaction.id"
        :transaction="transaction"
        :is-repeating-group="transaction.affected_transactions > 1"
      />
    </ul>
  </div>
</template>

<script>
import TransactionListGroupRow from './TransactionListGroupRow/TransactionListGroupRow'
export default {
  components: {
    TransactionListGroupRow
  },

  computed: {
    transactionsJustCreated () {
      return this.$store.getters['transaction/getTransactionsJustCreated']
    }
  }
}
</script>

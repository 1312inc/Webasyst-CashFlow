<template>
  <div>
    <TransactionListUpcoming class="custom-mb-24" />
    <TransactionListIncoming />
  </div>
</template>

<script>
import TransactionListIncoming from '@/components/TransactionListIncoming'
import TransactionListUpcoming from '@/components/TransactionListUpcoming'

export default {
  components: {
    TransactionListIncoming,
    TransactionListUpcoming
  }
}
</script>

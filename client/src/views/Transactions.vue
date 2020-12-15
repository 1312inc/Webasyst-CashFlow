<template>
  <div>
    <TransactionList />
  </div>
</template>

<script>
import moment from 'moment'
import TransactionList from '@/components/TransactionList'

export default {
  components: {
    TransactionList
  },

  created () {
    this.$store.commit('transaction/updateQueryParams', { from: moment().add(-3, 'Y').format('YYYY-MM-DD'), to: moment().format('YYYY-MM-DD'), filter: '' })
  }

}
</script>

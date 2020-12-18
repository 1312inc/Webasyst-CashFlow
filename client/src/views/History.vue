<template>
  <div>
    <h1>History</h1>
    <TransactionControls class="custom-mb-24" />
    <TransactionListIncoming />
  </div>
</template>

<script>
import moment from 'moment'
import TransactionControls from '@/components/TransactionControls'
import TransactionListIncoming from '@/components/TransactionListIncoming'

export default {
  components: {
    TransactionControls,
    TransactionListIncoming
  },

  data () {
    return {
      paramsBus: {}
    }
  },

  mounted () {
    this.paramsBus = this.$store.state.transaction.queryParams

    this.$store.commit('transaction/updateQueryParams', {
      from: moment().add(-3, 'Y').format('YYYY-MM-DD'),
      to: moment().format('YYYY-MM-DD'),
      filter: ''
    })
  },

  beforeDestroy () {
    this.$store.commit('transaction/updateQueryParams', {
      ...this.paramsBus,
      silent: true
    })
  }
}
</script>

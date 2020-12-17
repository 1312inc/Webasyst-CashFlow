<template>
  <div>
    <TransactionControls class="custom-mb-24" />
    <TransactionList />
  </div>
</template>

<script>
import moment from 'moment'
import TransactionControls from '@/components/TransactionControls'
import TransactionList from '@/components/TransactionList'

export default {
  components: {
    TransactionControls,
    TransactionList
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

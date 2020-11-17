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

  async created () {
    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams') {
        this.$store.dispatch('transaction/getList')
      }
    })

    this.$store.commit('transaction/updateQueryParams', { from: moment().add(-3, 'Y').format('YYYY-MM-DD'), to: moment().add(1, 'd').format('YYYY-MM-DD'), filter: '' })
  },

  beforeDestroy () {
    this.unsubscribe()
  }

}
</script>

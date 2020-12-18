<template>
  <div>
    <h1>{{ $t('upnext') }}</h1>
    <TransactionControls class="custom-mb-24" />
    <TransactionListIncoming :upcoming="true" :reverse="true" :grouping="false" />
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
      from: moment().add(-1, 'd').format('YYYY-MM-DD'),
      to: moment().add(7, 'd').format('YYYY-MM-DD'),
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

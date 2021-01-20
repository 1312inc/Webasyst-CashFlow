<template>
  <div>
    <h1>{{ $t('upnext') }}</h1>
    <TransactionControls class="custom-mb-24" />
    <div class="flexbox">
      <div class="wide">
        <TransactionListIncoming :upcoming="true" :reverse="true" :grouping="false" />
      </div>
      <AmChartPieStickyContainer class="width-40" />
    </div>
  </div>
</template>

<script>
import moment from 'moment'
import TransactionControls from '@/components/TransactionControls'
import TransactionListIncoming from '@/components/TransactionListIncoming'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    TransactionControls,
    TransactionListIncoming,
    AmChartPieStickyContainer
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

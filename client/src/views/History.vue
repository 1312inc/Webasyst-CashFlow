<template>
  <div>
    <h1>{{ $t('history') }}</h1>
    <TransactionControls class="custom-mb-24" />
    <div class="flexbox">
      <div class="wide">
        <TransactionList />
      </div>
      <AmChartPieStickyContainer class="width-40" />
    </div>
  </div>
</template>

<script>
import moment from 'moment'
import TransactionControls from '@/components/TransactionControls'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    TransactionControls,
    TransactionList,
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

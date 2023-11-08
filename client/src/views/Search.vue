<template>
  <div>
    <ChartHeader :show-controls="false">
      <template #title>
        <h1 class="">
          {{ $route.query.text }}<span v-if="!transactions.length && !loading">: {{ $t('notFound') }}</span>
        </h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :show-today-group="false"
          :show-future-group="false"
          :grouping="false"
          :visible-select-checkbox="true"
          :show-founded-count="true"
        />
      </div>
      <AmChartPieStickyContainer :selected-only-mode="true" />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },
  mixins: [routerTransitionMixin],

  computed: {
    transactions () {
      return this.$store.state.transaction.transactions.data
    },

    loading () {
      return this.$store.state.transaction.loading
    }
  },

  watch: {
    $route: 'makeSearch'
  },

  mounted () {
    this.makeSearch()
  },

  methods: {
    makeSearch () {
      this.$store.dispatch('transaction/fetchTransactions', {
        from: '',
        to: '',
        offset: 0,
        filter: `search/${this.$route.query.text}`
      })
    }
  }
}
</script>

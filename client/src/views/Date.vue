<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $moment($route.params.date).format('LL') }}
        </h1>
      </template>
    </ChartHeader>
    <div style="position: sticky; top: 4rem;z-index: 999;background-color: var(--background-color-blank);">
      <TransactionControls />
    </div>
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :show-future-group="false"
          :show-today-group="false"
        />
      </div>
      <AmChartPieStickyContainer />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'
import TransactionControls from '@/components/TransactionControls'

export default {

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer,
    TransactionControls
  },
  mixins: [routerTransitionMixin],

  mounted () {
    this.$store.dispatch('transaction/fetchTransactions', {
      from: this.$route.params.date,
      to: this.$route.params.date,
      offset: 0,
      filter: ''
    })
  }
}
</script>

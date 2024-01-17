<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $t("allTransactions") }}
        </h1>
      </template>
    </ChartHeader>
    <TransactionControls />
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :show-yesterday-group="true"
          :show-overdue-group="true"
          :show-tomorrow-group="true"
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
      from: '',
      to: this.$moment().format('YYYY-MM-DD'),
      offset: 0,
      filter: ''
    })
  }
}
</script>

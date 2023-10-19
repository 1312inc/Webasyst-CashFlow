<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">
          {{ $t("allTransactions") }}
        </h1>
      </template>
    </ChartHeader>
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :show-yesterday-group="true"
          :show-overdue-group="true"
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

export default {

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },
  mixins: [routerTransitionMixin],

  mounted () {
    this.$store.dispatch('transaction/fetchTransactions', {
      from: '',
      to: this.$moment().add(3, 'M').format('YYYY-MM-DD'),
      offset: 0,
      filter: ''
    })
  }
}
</script>

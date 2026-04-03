<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $t("allTransactions") }}
        </h1>
      </template>
    </ChartHeader>
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
import { DEFAULT_FUTURE_PERIOD } from '../utils/constants'

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
      to: DEFAULT_FUTURE_PERIOD,
      offset: 0,
      filter: ''
    })
  }
}
</script>

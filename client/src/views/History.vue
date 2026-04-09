<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $t("history") }}
        </h1>
        <TransactionControls />
      </template>
    </ChartHeader>
    <TransactionControlsSticky />
    <div class="flexbox space-24">
      <div class="wide">
        <TransactionList
          :show-today-group="false"
          :show-future-group="false"
          :show-yesterday-group="true"
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
import TransactionControls from '../components/TransactionControls.vue'
import TransactionControlsSticky from '../components/TransactionControlsSticky.vue'

export default {

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer,
    TransactionControls,
    TransactionControlsSticky

  },
  mixins: [routerTransitionMixin],

  mounted () {
    this.$store.dispatch('transaction/fetchTransactions', {
      from: '',
      to: this.$moment().add(-1, 'd').format('YYYY-MM-DD'),
      offset: 0,
      filter: ''
    })
  }
}
</script>

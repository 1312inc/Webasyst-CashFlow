<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <ChartHeader>
      <template #title>
        <h1 class="">
          {{ $t("allTransactions") }}
        </h1>
        <TransactionControls />
      </template>
    </ChartHeader>
    <TransactionControlsSticky />
    <div class="flexbox space-24">
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
import TransactionControls from '../components/TransactionControls.vue'
import TransactionControlsSticky from '../components/TransactionControlsSticky.vue'
import { DEFAULT_FUTURE_PERIOD } from '../utils/constants'

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
      to: DEFAULT_FUTURE_PERIOD,
      offset: 0,
      filter: ''
    })
  },

  metaInfo () {
    return {
      title: this.$t('transactions'),
      titleTemplate: `%s – ${window.appState?.accountName || ''}`
    }
  }
}
</script>

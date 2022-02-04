<template>
  <div>
    <ChartHeader
      :showControls="false"
    >
      <template v-slot:title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">
          {{ $t("trash") }}
        </h1>
        <p class="small custom-px-16-mobile">
          {{ $t("trashInfo") }}
        </p>
      </template>
    </ChartHeader>

    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :showTodayGroup="false"
          :showFutureGroup="false"
          :grouping="false"
          :visibleSelectCheckbox="true"
          :showFoundedCount="true"
        />
      </div>
      <AmChartPieStickyContainer
        :selectedOnlyMode="true"
        class="width-40"
      />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },

  provide: {
    archive: true
  },

  mounted () {
    this.$store.dispatch('transaction/fetchTransactions', {
      from: '',
      to: this.$moment().format('YYYY-MM-DD'),
      offset: 0,
      filter: 'trash/0'
    })
  }
}
</script>

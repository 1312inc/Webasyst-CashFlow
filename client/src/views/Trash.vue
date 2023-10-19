<template>
  <div>
    <ChartHeader
      :show-controls="false"
    >
      <template #title>
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
          :show-today-group="false"
          :show-future-group="false"
          :grouping="false"
          :visible-select-checkbox="true"
          :show-founded-count="true"
        />
      </div>
      <AmChartPieStickyContainer
        :selected-only-mode="true"
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

  components: {
    ChartHeader,
    TransactionList,
    AmChartPieStickyContainer
  },
  mixins: [routerTransitionMixin],

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

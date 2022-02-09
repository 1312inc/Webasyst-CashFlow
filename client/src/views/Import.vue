<template>
  <div>
    <ChartHeader
      :showControls="false"
    >
      <template v-slot:title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">
          {{ $t("importResults") }}
        </h1>
        <div v-if="importInfo" class="gray">{{ importInfo }}</div>
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

  computed: {
    importInfo () {
      const importInfo = new URL(location.href).searchParams.get('importinfo')
      return importInfo ? atob(importInfo) : null
    }
  },

  mounted () {
    this.$store.dispatch('transaction/fetchTransactions', {
      from: '',
      to: '',
      offset: 0,
      filter: `import/${this.$route.params.id}`
    })
  }
}
</script>

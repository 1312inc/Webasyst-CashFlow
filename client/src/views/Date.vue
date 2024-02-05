<template>
  <div>
    <h2
      v-if="!$helper.isDesktopEnv"
      class="custom-m-12 flexbox space-8"
    >
      <a @click.prevent="$router.push({ name: 'Calendar' })">
        <i class="fas fa-arrow-circle-left text-light-gray" />
      </a>
      {{ $moment($route.params.date).format('LL') }}
    </h2>
    <ChartHeader>
      <template #title>
        <h1 class="flexbox space-8">
          <a
            class="mobile-only"
            @click.prevent="$router.push({ name: 'Calendar' })"
          >
            <i class="fas fa-arrow-circle-left text-light-gray" />
          </a>
          {{ $moment($route.params.date).format('LL') }}
        </h1>
      </template>
    </ChartHeader>
    <TransactionControls :default-date="$route.params.date" />
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

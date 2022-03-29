<template>
  <div v-if="entity">
    <ChartHeader>
      <template v-slot:title>
        <h1
          class="flexbox space-12 items-middle custom-m-0 custom-px-16-mobile custom-pt-16-mobile"
        >
          <img v-if="entity.entity_icon" :src="entity.entity_icon" class="userpic" style="width:40px;" />
          <a :href="entity.entity_url" target="_blank">{{
            entity.entity_name
          }}</a>
        </h1>
      </template>
      <template v-slot:controls>
        <ChartHeaderControls />
      </template>
    </ChartHeader>
    <AmChartContainer />
    <DetailsDashboard />
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :showFutureGroup="false"
          :showYesterdayGroup="false"
          :showOverdueGroup="false"
          :showTodayGroup="false"
        />
      </div>
      <AmChartPieStickyContainer />
    </div>
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import ChartHeaderControls from '@/components/ChartHeaderControls'
import AmChartContainer from '@/components/Charts/AmChartContainer'
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import TransactionList from '@/components/TransactionList/TransactionList'
import AmChartPieStickyContainer from '@/components/Charts/AmChartPieStickyContainer'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'
import api from '@/plugins/api'

// TODO: Add vue-meta

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    ChartHeaderControls,
    AmChartContainer,
    DetailsDashboard,
    TransactionList,
    AmChartPieStickyContainer
  },

  metaInfo () {
    return {
      title: this.entity?.entity_name || '',
      titleTemplate: `%s – ${window.appState?.accountName || ''}`
    }
  },

  beforeRouteLeave (to, from, next) {
    this.$store.commit('entity/resetEntity')
    next()
  },

  computed: {
    entity () {
      return this.$store.state.entity.entity
    }
  },

  watch: {
    $route: 'fetch'
  },

  created () {
    this.fetch()
  },

  methods: {
    async fetch () {
      try {
        const { data } = await api.get(
      `cash.system.getExternalEntity?source=${this.$route.meta.getExternalEntitySource}&id=${this.$route.params.id}`
        )
        this.$store.commit('entity/setEntity', data)

        this.$store.commit('transaction/updateQueryParams', { filter: this.$route.meta.fetchTransactionsFilter(this.entity.entity_id) })
        this.$store.dispatch('transaction/getChartData')

        this.$store.dispatch('transaction/fetchTransactions', {
          from: '',
          to: this.$moment().format('YYYY-MM-DD'),
          offset: 0,
          filter: this.$route.meta.fetchTransactionsFilter(this.entity.entity_id)
        })
      } catch (error) {
        if (error.response.status === 404) {
          this.$router.replace({ name: 'NotFound' })
        }
      }
    }
  }
}
</script>

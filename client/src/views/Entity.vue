<template>
  <div
    v-if="error"
    class="c-header custom-m-12 custom-ml-32 custom-ml-12-mobile"
  >
    <h1>{{ $route.params.id }}: {{ error }}</h1>
  </div>
  <div v-else-if="entity">
    <ChartHeader>
      <template #title>
        <h1 class="flexbox space-12 middle ">
          <img
            v-if="entity.entity_icon"
            :src="entity.entity_icon"
            style="height: 40px; object-fit: contain;"
            :class="{ 'userpic': $route.meta.getExternalEntitySource === 'contacts' }"
          >
          <a
            :href="entity.entity_url"
            target="_blank"
          >{{
            entity.entity_name
          }}</a>
        </h1>
      </template>
      <template
        v-if="$route.meta.showChart"
        #controls
      >
        <ChartHeaderControls />
      </template>
    </ChartHeader>
    <TransactionControls />
    <template v-if="$route.meta.showChart">
      <AmChartContainer />
      <DetailsDashboard />
    </template>
    <div class="flexbox">
      <div class="wide">
        <TransactionList
          :show-future-group="false"
          :show-yesterday-group="false"
          :show-overdue-group="false"
          :show-today-group="false"
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
import TransactionControls from '@/components/TransactionControls'
import api from '@/plugins/api'

export default {

  components: {
    ChartHeader,
    ChartHeaderControls,
    AmChartContainer,
    DetailsDashboard,
    TransactionList,
    AmChartPieStickyContainer,
    TransactionControls
  },

  mixins: [routerTransitionMixin],

  beforeRouteLeave (to, from, next) {
    this.$store.commit('entity/resetEntity')
    next()
  },

  data () {
    return {
      error: null
    }
  },

  metaInfo () {
    return {
      title: this.entity?.entity_name || '',
      titleTemplate: `%s – ${window.appState?.accountName || ''}`
    }
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
      this.error = null

      try {
        const { data } = await api.get(
          `cash.system.getExternalEntity?source=${this.$route.meta.getExternalEntitySource}&id=${this.$route.params.id}`
        )

        if (data.error) {
          this.handleResponseError(data)
          return
        }

        this.$store.commit('entity/setEntity', data)

        this.$store.commit('transaction/updateQueryParams', { filter: this.$route.meta.fetchTransactionsFilter(this.entity.entity_id) })
        if (this.$route.meta.showChart) {
          this.$store.dispatch('transaction/getChartData')
        }

        this.$store.dispatch('transaction/fetchTransactions', {
          from: '',
          to: this.$moment().format('YYYY-MM-DD'),
          offset: 0,
          filter: this.$route.meta.fetchTransactionsFilter(this.entity.entity_id)
        })
      } catch (error) {
        this.handleResponseError(error.response.data)
        // if (error.response.status === 404) {
        //   this.$router.replace({ name: 'NotFound' })
        // }
      }
    },

    handleResponseError (error) {
      this.error = error.error_description ?? 'Error'
    }
  }
}
</script>

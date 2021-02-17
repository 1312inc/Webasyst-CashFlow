<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0">{{ $route.query.text }}</h1>
      </template>
    </ChartHeader>
    <TransactionList />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import TransactionList from '@/components/TransactionList/TransactionList'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    ChartHeader,
    TransactionList
  },

  data () {
    return {
      paramsBus: {}
    }
  },

  watch: {
    '$route' () {
      this.makeSearch()
    }
  },

  mounted () {
    this.paramsBus = this.$store.state.transaction.queryParams
    this.makeSearch()
  },

  beforeDestroy () {
    this.$store.commit('transaction/updateQueryParams', {
      ...this.paramsBus,
      silent: true
    })
  },

  methods: {
    makeSearch () {
      this.$store.commit('transaction/updateQueryParams', {
        from: '',
        to: '',
        filter: `search/${this.$route.query.text}`
      })
    }
  }

}
</script>

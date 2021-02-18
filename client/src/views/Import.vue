<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0">{{ $t("importResults") }}</h1>
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

  mounted () {
    this.paramsBus = this.$store.state.transaction.queryParams
    this.fetchData()
  },

  beforeDestroy () {
    this.$store.commit('transaction/updateQueryParams', {
      ...this.paramsBus,
      silent: true
    })
  },

  methods: {
    fetchData () {
      this.$store.commit('transaction/updateQueryParams', {
        from: '',
        to: '',
        filter: `import/${this.$route.params.id}`
      })
    }
  }
}
</script>

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

  watch: {
    $route: 'makeSearch'
  },

  mounted () {
    this.makeSearch()
  },

  methods: {
    makeSearch () {
      this.$store.dispatch('transaction/fetchTransactions', {
        from: '',
        to: '',
        offset: 0,
        filter: `search/${this.$route.query.text}`
      })
    }
  }

}
</script>

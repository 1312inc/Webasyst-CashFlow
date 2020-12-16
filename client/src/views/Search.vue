<template>
    <div>
        <h1 class="custom-mb-24">{{ $route.query.text }}</h1>
        <TransactionList />
    </div>
</template>

<script>
import TransactionList from '@/components/TransactionList'

export default {
  components: {
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

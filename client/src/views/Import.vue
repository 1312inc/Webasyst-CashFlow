<template>
    <div>
        <h1>{{ $t('importResults') }}</h1>
        <TransactionControls class="custom-mb-24" />
        <TransactionList />
    </div>
</template>

<script>
import TransactionControls from '@/components/TransactionControls'
import TransactionList from '@/components/TransactionList'
import routerTransitionMixin from '@/mixins/routerTransitionMixin'

export default {
  mixins: [routerTransitionMixin],

  components: {
    TransactionControls,
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

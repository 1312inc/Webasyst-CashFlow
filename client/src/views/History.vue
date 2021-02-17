<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0">{{ $t("history") }}</h1>
      </template>
    </ChartHeader>
    <TransactionList />
  </div>
</template>

<script>
import moment from 'moment'
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

    this.$store.commit('transaction/updateQueryParams', {
      from: moment().add(-3, 'Y').format('YYYY-MM-DD'),
      to: moment().format('YYYY-MM-DD'),
      filter: ''
    })
  },

  beforeDestroy () {
    this.$store.commit('transaction/updateQueryParams', {
      ...this.paramsBus,
      silent: true
    })
  }
}
</script>

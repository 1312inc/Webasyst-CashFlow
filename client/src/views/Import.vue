<template>
  <div>
    <ChartHeader>
      <template v-slot:title>
        <h1 class="custom-m-0 custom-px-16-mobile custom-pt-16-mobile">{{ $t("importResults") }}</h1>
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

<template>
  <div>
    <AmChart />

    <DetailsDashboard v-if="showDetailsDashboard"></DetailsDashboard>

    <TransactionList />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList'

export default {
  components: {
    AmChart,
    DetailsDashboard,
    TransactionList
  },

  computed: {
    ...mapState('transaction', ['detailsInterval']),

    showDetailsDashboard () {
      return this.detailsInterval.from
    }
  }
}
</script>

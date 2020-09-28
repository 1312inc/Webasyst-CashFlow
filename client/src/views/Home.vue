<template>
  <div>

    <AmChart></AmChart>

    <DetailsDashboard v-if="showDetailsDashboard"></DetailsDashboard>

    <TransactionList :listItems="cuttedList" />

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
    ...mapState('transaction', ['interval', 'detailsInterval', 'transactions']),

    showDetailsDashboard () {
      return this.detailsInterval.from
    },

    cuttedList () {
      return this.transactions.slice(0, 30)
    }
  }
}
</script>

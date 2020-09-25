<template>
  <div>

    <AmChart></AmChart>

    <DetailsDashboard v-if="detailsDate.from"></DetailsDashboard>

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
    ...mapState('transaction', ['detailsDate', 'listItems']),

    cuttedList () {
      return this.listItems.slice(0, 30)
    }
  }
}
</script>

<template>
  <div>
    <AmChart />
    <DetailsDashboard></DetailsDashboard>
    <TransactionList />
  </div>
</template>

<script>
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList'

export default {
  components: {
    AmChart,
    DetailsDashboard,
    TransactionList
  }
}
</script>

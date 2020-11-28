<template>
  <div>
    <ChartHeader />
    <AmChart />
    <DetailsDashboard />
    <TransactionList />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import AmChart from '@/components/AmChart'
import DetailsDashboard from '@/components/DetailsDashboard'
import TransactionList from '@/components/TransactionList'

export default {
  components: {
    ChartHeader,
    AmChart,
    DetailsDashboard,
    TransactionList
  }
}
</script>

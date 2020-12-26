<template>
  <div class="flexbox custom-mb-12">
    <div v-if="$helper.isDesktopEnv" class="wide">
      <ChartHeaderTitle />
      <TransactionControls />
    </div>
    <div class="flexbox space-4">
      <div>
        <Dropdown type="from" class="custom-mb-4" />
        <AmountForPeriod type="income" period="from" class="custom-mb-4" />
        <AmountForPeriod type="expense" period="from" />
      </div>
      <div>
        <Dropdown type="to" class="custom-mb-4" />
        <AmountForPeriod type="income" period="to" class="custom-mb-4" />
        <AmountForPeriod type="expense" period="to" />
      </div>
    </div>
  </div>
</template>

<script>
import ChartHeaderTitle from '@/components/ChartHeaderTitle'
import TransactionControls from '@/components/TransactionControls'

import Dropdown from '@/components/Dropdown'
import AmountForPeriod from '@/components/AmountForPeriod'

export default {
  components: {
    ChartHeaderTitle,
    TransactionControls,
    Dropdown,
    AmountForPeriod
  }
}
</script>

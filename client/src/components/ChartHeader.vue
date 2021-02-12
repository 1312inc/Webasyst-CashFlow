<template>
  <div class="c-header">
    <div class="flexbox">
        <div v-if="$helper.isDesktopEnv" class="wide">
          <ChartHeaderTitle />
          <TransactionControls />
        </div>
        <div class="c-period-dropdowns">
            <div class="flexbox space-12">
              <div>
                <Dropdown type="from" class="custom-mb-8" />
                <AmountForPeriod type="income" period="from" class="custom-mb-4" />
                <AmountForPeriod type="expense" period="from" />
              </div>
              <div>
                <Dropdown type="to" class="custom-mb-8" />
                <AmountForPeriod type="income" period="to" class="custom-mb-4" />
                <AmountForPeriod type="expense" period="to" />
              </div>
            </div>
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

<style lang="scss">
.c-period-dropdowns {
  position: absolute;
  right: 0;

  @media (max-width: 768px) {
    position: relative;
    right: auto;
    width: 100%;
  }
}
.c-period-dropdowns > .flexbox {
  @media (max-width: 768px) {
    padding: 1rem;
  }
}
.c-period-dropdowns > .flexbox > div {
  @media (max-width: 768px) {
    flex: 1;
  }
}
.c-period-dropdowns > .flexbox > div > .wa-select {
  @media (max-width: 768px) {
    width: 100%;
  }
}
</style>

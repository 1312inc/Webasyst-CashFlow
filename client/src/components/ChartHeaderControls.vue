<template>
  <div class="c-period-dropdowns-container">
    <div
      class="flexbox vertical-mobile space-12"
      style="align-items: start;"
    >
      <CurrencyToggler />
      <div class="c-period-dropdowns flexbox fixed space-12 width-100-mobile">
        <div>
          <Dropdown
            type="from"
            class="custom-m-0 custom-mb-8 width-100-mobile"
          />
          <AmountForPeriod period="from" />
        </div>
        <div>
          <Dropdown
            type="to"
            class="custom-m-0 custom-mb-8 width-100-mobile"
          />
          <AmountForPeriod period="to" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CurrencyToggler from '@/components/CurrencyToggler'
import Dropdown from '@/components/Inputs/Dropdown'
import AmountForPeriod from '@/components/PeriodAmount/AmountForPeriod'

export default {
  components: {
    CurrencyToggler,
    Dropdown,
    AmountForPeriod
  }
}
</script>

<style lang="scss">
.c-period-dropdowns-container {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 9999;

  @media (max-width: 760px) {
    position: relative;
    top: auto;
    right: auto;
    width: 100%;
  }
}

.c-period-dropdowns-container button {
  margin: 0 !important;
}
</style>

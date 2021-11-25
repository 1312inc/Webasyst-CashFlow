<template>
  <div class="c-period-dropdowns">
    <div class="flexbox vertical-mobile space-12" style="align-items: start">
      <CurrencyToggler class="custom-mb-12-mobile" />
      <div class="flexbox fixed space-12 width-100-mobile">
        <div>
          <Dropdown type="from" class="custom-m-0 custom-mb-8 width-100-mobile" />
          <AmountForPeriod period="from" />
        </div>
        <div>
          <Dropdown type="to" class="custom-m-0 custom-mb-8 width-100-mobile" />
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
.c-period-dropdowns {
  @media (max-width: 760px) {
    position: relative;
    right: auto;
    width: 100%;
  }
}
.c-period-dropdowns > .flexbox {
  @media (max-width: 760px) {
    padding: 1rem;
  }
}
.c-period-dropdowns > .flexbox > div {
  @media (max-width: 760px) {
    flex: 1;
  }
}
.c-period-dropdowns > .flexbox > div > .wa-select {
  @media (max-width: 760px) {
    width: 100%;
  }
}
</style>

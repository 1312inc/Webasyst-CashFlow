<template>
  <div class="c-period-dropdowns-container">
    <div class="flexbox vertical-mobile space-12">
      <button
        class="button light-gray nowrap desktop-only"
        @click="printPage()"
      >
        <i class="fas fa-print" />
      </button>

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

<script setup>
import CurrencyToggler from '@/components/CurrencyToggler'
import Dropdown from '@/components/Inputs/Dropdown'
import AmountForPeriod from '@/components/PeriodAmount/AmountForPeriod'

function printPage () {
  window.print()
}
</script>

<style lang="scss">
.c-period-dropdowns-container button {
  margin: 0 !important;
}
</style>

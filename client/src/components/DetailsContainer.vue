<script setup>
import { computed } from 'vue'
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import BlankBox from '../components/BlankBox.vue'
import DetailsContainerTarget from '@/components/DetailsContainerTarget.vue'
import store from '@/store'
import { getIntervalFromLabel } from '@/utils/getDateFromLocalStorage'

const showFuture = computed(() => {
  if (store.getters['transaction/isDetailsMode']) return false
  const to = store.state.transaction.chartInterval.to
  return Boolean(to) && getIntervalFromLabel('to') !== '0_M'
})
</script>

<template>
  <div class="c-details-container flexbox space-24 custom-mb-24">
    <div class="wide">
      <div class="c-details-modes flexbox">
        <div class="wide">
          <BlankBox :disable-bottom-margin="true">
            <DetailsDashboard mode="from" />
          </BlankBox>
        </div>
        <div
          v-if="showFuture"
          class="wide"
        >
          <BlankBox
            :transparent="true"
            :disable-bottom-margin="true"
          >
            <DetailsDashboard mode="to" />
          </BlankBox>
        </div>
      </div>
    </div>
    <DetailsContainerTarget />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import BlankBox from '../components/BlankBox.vue'
import DetailsContainerTarget from '@/components/DetailsContainerTarget.vue'
import store from '@/store'

const showFuture = computed(() => !store.getters['transaction/isDetailsMode'])
</script>

<template>
  <div class="c-details-container flexbox space-24 custom-mb-24">
    <div class="wide">
      <BlankBox :disable-bottom-margin="true">
        <div class="c-details-modes flexbox">
          <div class="wide">
            <DetailsDashboard mode="from" />
          </div>
          <div
            v-if="showFuture"
            class="wide"
          >
            <DetailsDashboard mode="to" />
          </div>
        </div>
      </BlankBox>
    </div>
    <DetailsContainerTarget />
  </div>
</template>

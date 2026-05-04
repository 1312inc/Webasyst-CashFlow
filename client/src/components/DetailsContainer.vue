<script setup>
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import BlankBox from '../components/BlankBox.vue'
import DetailsContainerTarget from '@/components/DetailsContainerTarget.vue'
import { useRoute } from 'vue-router/composables'
import { computed } from 'vue'

const route = useRoute()
const isAccountPage = computed(() => route.name === 'Account')

</script>

<template>
  <div class="c-details-container flexbox space-24 custom-mb-24">
    <div class="wide">
      <BlankBox :disable-bottom-margin="true">
        <DetailsDashboard />
      </BlankBox>
    </div>
    <DetailsContainerTarget v-if="!isAccountPage" />
  </div>
</template>

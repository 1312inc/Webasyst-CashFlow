<script setup>
import DetailsDashboard from '@/components/Dashboard/DetailsDashboard'
import BlankBox from '../components/BlankBox.vue'
import { useStorage } from '@vueuse/core'
import AmChartTarget from './Charts/AmChartTarget.vue'
import { useRoute } from 'vue-router/composables'
import { computed } from 'vue'

const route = useRoute()
const cashTargetBlockHidden = useStorage('cashTargetBlockHidden', { value: false, expiredAt: '' }, localStorage)

const isCategoryPage = computed(() => route.name === 'Category')

// Сбросить, если 30 дней прошло
if (cashTargetBlockHidden.value.value && cashTargetBlockHidden.value.expiredAt) {
  const expired = new Date(cashTargetBlockHidden.value.expiredAt) < new Date()
  if (expired) {
    cashTargetBlockHidden.value.value = false
    cashTargetBlockHidden.value.expiredAt = ''
  }
}

function closeTarget () {
  const expiredAt = new Date()
  expiredAt.setDate(expiredAt.getDate() + 30)

  cashTargetBlockHidden.value.value = true
  cashTargetBlockHidden.value.expiredAt = expiredAt.toISOString()
}
</script>

<template>
  <div
    v-if="!isCategoryPage"
    class="c-details-container flexbox space-24 custom-mb-24"
  >
    <div class="wide">
      <BlankBox :disable-bottom-margin="true">
        <DetailsDashboard />
      </BlankBox>
    </div>
    <div
      v-if="!cashTargetBlockHidden.value"
      class="c-details-container__target"
    >
      <BlankBox :disable-bottom-margin="true">
        <div
          class="flexbox middle"
          style="height: 100%;"
        >
          <a
            href="#"
            class="c-details-container__target__close"
            @click.prevent="closeTarget"
          >
            <i class="fas fa-times gray" />
          </a>
          <div>
            <AmChartTarget />
            <h5 class="align-center custom-mt-0 custom-mb-12">
              {{ $t('detailsTargetDescTitle') }}
            </h5>
            <p class="small gray align-center width-90 custom-mx-auto custom-my-12">
              {{ $t('detailsTargetDesc') }}
            </p>
            <div class="align-center custom-my-16">
              <a
                :href="`${$helper.baseUrl}upgrade/`"
                class="button small green"
              >{{ $t('detailsTargetDescLink') }}</a>
            </div>
          </div>
        </div>
      </BlankBox>
    </div>
  </div>
</template>

<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <ChartHeader :show-controls="false">
      <template #title>
        <h1>{{ $t("scenarios") }}</h1>
      </template>
    </ChartHeader>
    <div
      v-if="!$appState.isPremium"
      class="settings-table-wrap custom-px-24 custom-ml-4 custom-px-12-mobile"
    >
      <p class="small">
        {{ $t("scenariosHint") }}
      </p>
      <p class="small">
        {{ $t("scenariosHintDelete") }}
      </p>
      <div class="alert warning small">
        <i class="fas fa-star small" />
        {{ $t("planView.premiumAlert") }}
      </div>
    </div>
    <ScenariosTable v-else />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import ScenariosTable from '@/components/ScenariosTable.vue'
import { appStateService } from '@/services/appState'

export default {
  components: {
    ChartHeader,
    ScenariosTable
  },

  metaInfo () {
    return {
      title: this.$t('scenarios'),
      titleTemplate: appStateService.titleTemplate
    }
  }
}
</script>

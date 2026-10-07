<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <Forbidden v-if="!$appState.isPremium" />
  <div v-else>
    <ChartHeader :show-controls="false">
      <template #title>
        <h1>{{ $t("scenarios") }}</h1>
      </template>
    </ChartHeader>
    <ScenariosTable />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import ScenariosTable from '@/components/ScenariosTable.vue'
import Forbidden from '@/views/Forbidden.vue'
import { appStateService } from '@/services/appState'

export default {
  components: {
    ChartHeader,
    ScenariosTable,
    Forbidden
  },

  metaInfo () {
    return {
      title: this.$t('scenarios'),
      titleTemplate: appStateService.titleTemplate
    }
  }
}
</script>

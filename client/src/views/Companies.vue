<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <Forbidden v-if="!$appState.isPremium" />
  <div v-else>
    <ChartHeader :show-controls="false">
      <template #title>
        <h1>{{ $t("companies") }}</h1>
      </template>
    </ChartHeader>
    <CompaniesTable />
  </div>
</template>

<script>
import ChartHeader from '@/components/ChartHeader'
import CompaniesTable from '@/components/CompaniesTable.vue'
import Forbidden from '@/views/Forbidden.vue'
import { appStateService } from '@/services/appState'

export default {
  components: {
    ChartHeader,
    CompaniesTable,
    Forbidden
  },

  metaInfo () {
    return {
      title: this.$t('companies'),
      titleTemplate: appStateService.titleTemplate
    }
  }
}
</script>

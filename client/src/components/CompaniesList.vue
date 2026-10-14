<template>
  <DropdownWaFloating v-if="companies.length || scenarios.length">
    <template #toggler>
      <button
        class="c-dropdown-toggle-custom button width-100"
        :class="{ 'light-gray': !selectedCompany?.id && !selectedScenario }"
        :style="selectedScenario ? { backgroundColor: selectedScenario.color, color: '#fff' } : null"
      >
        {{ toggleLabel }}
      </button>
    </template>
    <template #default>
      <ul
        v-if="companies.length"
        class="menu"
      >
        <li
          v-for="company in items"
          :key="company.id"
          :class="{
            selected: company.id === companyContextService.companyId
          }"
        >
          <a @click.prevent="onCompanyClick(company)">
            <span
              v-if="company.id"
              class="icon"
            >
              <i
                class="fas fa-university"
              />
            </span>
            <span>{{ company.name }}</span>
          </a>
        </li>
      </ul>
      <template v-if="scenarios.length">
        <h5
          v-if="companies.length > 0"
          class="heading custom-mt-16"
        >
          {{ $t('scenarios') }}
        </h5>
        <ul class="menu">
          <li :class="{ selected: !scenarioContextService.scenarioId }">
            <a @click.prevent="onScenarioClick(0)">
              <span class="icon">
                <i
                  class="fas fa-code-branch"
                />
              </span>
              <span>{{ $t('defaultScenario') }}</span>
            </a>
          </li>
          <li
            v-for="scenario in scenarios"
            :key="scenario.id"
            :class="{ selected: scenario.id === scenarioContextService.scenarioId }"
          >
            <a @click.prevent="onScenarioClick(scenario.id)">
              <span class="icon">
                <i
                  class="fas fa-code-branch"
                  :style="{ color: scenario.color }"
                />
              </span>
              <span>{{ scenario.name }}</span>
            </a>
          </li>
        </ul>
      </template>
    </template>
  </DropdownWaFloating>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import DropdownWaFloating from './Inputs/DropdownWaFloating.vue'
import { i18n } from '@/plugins/locale'
import { companyContextService } from '../services/companyContext'
import { scenarioContextService } from '../services/scenarioContext'
import { useStore } from '../composables/useStore'

const store = useStore()

const companies = computed(() => store.getters['company/sortedCompanies'])
const scenarios = computed(() => store.getters['scenario/sortedScenarios'])

const selectedCompany = computed(() => {
  return items.value.find(company => company.id === companyContextService.companyId)
})

const selectedScenario = computed(() => {
  const scenarioId = scenarioContextService.scenarioId
  if (!scenarioId) return null
  return scenarios.value.find(scenario => scenario.id === scenarioId) || null
})

const items = computed(() => {
  return [
    ...(companies.value.length
      ? [{
          id: 0,
          name: i18n.t('allCompanies'),
          sort: -1
        }]
      : []),
    ...companies.value
  ]
})

const toggleLabel = computed(() => {
  const companyName = selectedCompany.value?.id ? selectedCompany.value.name : ''
  if (companyName && selectedScenario.value) {
    return `${companyName} / ${selectedScenario.value.name}`
  }
  if (selectedScenario.value) return selectedScenario.value.name
  if (companyName) return companyName
  if (!companies.value.length) return i18n.t('defaultScenario')
  return i18n.t('allCompanies')
})

const onCompanyClick = (company) => {
  companyContextService.companyId = company.id
  window.location.reload()
}

const onScenarioClick = (id) => {
  if (id === scenarioContextService.scenarioId) return
  scenarioContextService.scenarioId = id
  window.location.reload()
}

onMounted(async () => {
  const [, loaded] = await Promise.all([
    store.dispatch('company/getList'),
    store.dispatch('scenario/getList')
  ])
  if (loaded === false) return
  const scenarioId = scenarioContextService.scenarioId
  if (!scenarioId) return
  const exists = scenarios.value.some(scenario => scenario.id === scenarioId)
  if (!exists) {
    scenarioContextService.scenarioId = 0
    window.location.reload()
  }
})
</script>

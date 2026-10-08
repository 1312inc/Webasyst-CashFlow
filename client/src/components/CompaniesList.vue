<template>
  <DropdownWaFloating v-if="companies.length">
    <template #toggler>
      <button
        class="c-dropdown-toggle-custom button width-100"
        :class="{ 'light-gray': !selectedCompany?.id }"
      >
        {{ selectedCompany?.name || $t('companies') }}
      </button>
    </template>
    <template #default>
      <ul class="menu">
        <li
          v-for="company in items"
          :key="company.id"
          :class="{
            selected: company.id === companyContextService.companyId
          }"
        >
          <a @click.prevent="onCompanyClick(company)">{{ company.name }} </a>
        </li>
      </ul>
    </template>
  </DropdownWaFloating>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import DropdownWaFloating from './Inputs/DropdownWaFloating.vue'
import { i18n } from '@/plugins/locale'
import { companyContextService } from '../services/companyContext'
import { useStore } from '../composables/useStore'

const store = useStore()

const companies = computed(() => store.getters['company/sortedCompanies'])
const selectedCompany = computed(() => {
  return items.value.find(company => company.id === companyContextService.companyId)
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

const onCompanyClick = (company) => {
  companyContextService.companyId = company.id
  window.location.reload()
}

onMounted(async () => {
  await store.dispatch('company/getList')
})
</script>

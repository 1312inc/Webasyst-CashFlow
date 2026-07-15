<template>
  <DropdownWaFloating v-if="items.length > 1">
    <template #toggler>
      <button class="button light-gray">
        {{ selectedCompany?.name }}
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
import { onMounted, ref, computed } from 'vue'
import DropdownWaFloating from './Inputs/DropdownWaFloating.vue'
import api from '@/plugins/api'
import { i18n } from '@/plugins/locale'
import { companyContextService } from '../services/companyContext'

const companies = ref([])
const selectedCompany = computed(() => {
  return items.value.find(company => company.id === companyContextService.companyId)
})

const items = computed(() => {
  return [
    {
      id: 0,
      name: i18n.t('allCompanies'),
      sort: -1
    },
    ...companies.value
  ].sort((a, b) => a.sort - b.sort)
})

const onCompanyClick = (company) => {
  companyContextService.companyId = company.id
  window.location.reload()
}

onMounted(async () => {
  const { data } = await api.get(
    'cash.company.getList'
  )
  if (Array.isArray(data)) {
    companies.value = data
  }
})
</script>

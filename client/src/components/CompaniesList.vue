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
        <li>
          <a @click.prevent="onConfigureCompanies">{{ $t('configureCompanies') }}</a>
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
import { useRouter } from 'vue-router/composables'

const router = useRouter()
const store = useStore()

const companies = computed(() => store.getters['company/sortedCompanies'])
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
  ]
})

const onCompanyClick = (company) => {
  companyContextService.companyId = company.id
  window.location.reload()
}

const onConfigureCompanies = () => {
  router.push({ name: 'Companies' })
}

onMounted(async () => {
  await store.dispatch('company/getList')
})
</script>

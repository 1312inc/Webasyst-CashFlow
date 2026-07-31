<template>
  <div class="companies-table-wrap custom-px-24 custom-ml-4 custom-px-12-mobile">
    <div class="flexbox middle space-12 custom-mb-16">
      <input
        v-model="newName"
        class="bold"
        type="text"
        :placeholder="$t('addCompany')"
        :disabled="isBusy"
        @keyup.enter="onCreate"
      >
      <button
        class="button green"
        :disabled="isBusy || !newName.trim()"
        @click="onCreate"
      >
        {{ $t('add') }}
      </button>
    </div>

    <table
      class="bigdata companies-table"
      :class="{ loading: isLoading }"
    >
      <thead>
        <tr>
          <th class="handle-cell" />
          <th>{{ $t('companyName') }}</th>
          <th class="actions-cell" />
        </tr>
      </thead>
      <draggable
        :list="localCompanies"
        tag="tbody"
        handle=".company-sort-handle"
        ghost-class="ghost"
        :disabled="isBusy || editingId !== null"
        @end="onSortEnd"
      >
        <tr
          v-for="company in localCompanies"
          :key="company.id"
          :class="{ 'is-editing': editingId === company.id }"
        >
          <td class="handle-cell">
            <span class="company-sort-handle">
              <i class="fas fa-grip-vertical" />
            </span>
          </td>
          <td>
            <input
              v-if="editingId === company.id"
              ref="editInput"
              v-model="editName"
              class="bold full-width"
              type="text"
              :disabled="isBusy"
              @keyup.enter="saveEdit(company)"
              @keyup.esc="cancelEdit"
              @blur="onEditBlur(company)"
            >
            <template v-else>
              <span
                class="company-name"
                @click="startEdit(company)"
              >{{ company.name }}</span>
              <div
                v-if="accountsByCompanyId(company.id).length"
                class="company-accounts flexbox wrap"
              >
                <span
                  v-for="account in accountsByCompanyId(company.id)"
                  :key="account.id"
                  class="badge light-gray smaller company-account-chip"
                  :title="account.name"
                >
                  <img
                    v-if="isValidIcon(account.icon)"
                    :src="account.icon"
                    alt=""
                    class="company-account-chip-icon"
                  >
                  <i
                    v-else
                    class="fas company-account-chip-icon"
                    :class="accountIcon(account)"
                  />
                  {{ account.name }}
                </span>
              </div>
            </template>
          </td>
          <td class="actions-cell">
            <div class="flexbox middle space-8">
              <button
                v-if="editingId !== company.id"
                class="button light-gray small"
                :disabled="isBusy"
                :title="$t('update')"
                @click="startEdit(company)"
              >
                <i class="fas fa-pencil-alt" />
              </button>
              <button
                class="button light-gray small"
                :disabled="isBusy"
                :title="$t('delete')"
                @click="onDelete(company)"
              >
                <i class="fas fa-trash-alt" />
              </button>
            </div>
          </td>
        </tr>
      </draggable>
    </table>

    <p
      v-if="!isLoading && !localCompanies.length"
      class="gray custom-mt-16"
    >
      {{ $t('noCompanies') }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, getCurrentInstance } from 'vue'
import draggable from 'vuedraggable'
import { useStore } from '@/composables/useStore'
import { i18n } from '@/plugins/locale'
import currencyIcons from '@/utils/currencyIcons'

const store = useStore()
const { proxy } = getCurrentInstance()

const newName = ref('')
const localCompanies = ref([])
const editingId = ref(null)
const editName = ref('')
const editInput = ref(null)
const isLoading = ref(true)
const isBusy = ref(false)
const oldOrder = ref([])
const skipBlurSave = ref(false)

const sortedCompanies = computed(() => store.getters['company/sortedCompanies'])
const accounts = computed(() => store.state.account.accounts)

const accountsByCompanyId = (companyId) => {
  return accounts.value.filter(account => +account.company_id === +companyId)
}

const isValidIcon = (icon) => proxy.$helper.isValidHttpUrl(icon)

const accountIcon = (account) => {
  return currencyIcons[account.currency] || currencyIcons.default
}

watch(sortedCompanies, (companies) => {
  localCompanies.value = companies.map(c => ({ ...c }))
  if (!oldOrder.value.length) {
    oldOrder.value = companies.map(c => c.id)
  }
}, { immediate: true })

const startEdit = async (company) => {
  if (isBusy.value) return
  editingId.value = company.id
  editName.value = company.name
  await nextTick()
  const input = Array.isArray(editInput.value) ? editInput.value[0] : editInput.value
  input?.focus()
  input?.select()
}

const cancelEdit = () => {
  editingId.value = null
  editName.value = ''
}

const saveEdit = async (company) => {
  if (isBusy.value || editingId.value !== company.id) return

  const name = editName.value.trim()
  if (!name) {
    cancelEdit()
    return
  }
  if (name === company.name) {
    cancelEdit()
    return
  }

  skipBlurSave.value = true
  isBusy.value = true
  try {
    await store.dispatch('company/update', { id: company.id, name })
    cancelEdit()
  } finally {
    isBusy.value = false
    skipBlurSave.value = false
  }
}

const onEditBlur = (company) => {
  if (skipBlurSave.value) return
  saveEdit(company)
}

const onCreate = async () => {
  const name = newName.value.trim()
  if (!name || isBusy.value) return

  isBusy.value = true
  try {
    const result = await store.dispatch('company/create', { name })
    if (result) {
      newName.value = ''
      oldOrder.value = sortedCompanies.value.map(c => c.id)
    }
  } finally {
    isBusy.value = false
  }
}

const onDelete = async (company) => {
  if (isBusy.value) return
  if (!confirm(i18n.t('deleteWarning.company'))) return

  isBusy.value = true
  try {
    await store.dispatch('company/delete', company.id)
    oldOrder.value = sortedCompanies.value.map(c => c.id)
    if (editingId.value === company.id) {
      cancelEdit()
    }
  } finally {
    isBusy.value = false
  }
}

const onSortEnd = async () => {
  const newOrder = localCompanies.value.map(c => c.id)
  if (!newOrder.length || JSON.stringify(newOrder) === JSON.stringify(oldOrder.value)) {
    return
  }

  isBusy.value = true
  try {
    await store.dispatch('company/sort', {
      oldOrder: oldOrder.value,
      newOrder
    })
    oldOrder.value = newOrder
  } finally {
    isBusy.value = false
  }
}

onMounted(async () => {
  isLoading.value = true
  await store.dispatch('company/getList')
  oldOrder.value = sortedCompanies.value.map(c => c.id)
  isLoading.value = false
})
</script>

<style scoped>
.companies-table-wrap {
  max-width: 800px;
}

.companies-table .handle-cell {
  width: 2rem;
}

.companies-table .actions-cell {
  width: 6rem;
  text-align: right;
}

.company-sort-handle {
  cursor: grab;
  color: var(--gray);
  user-select: none;
  display: inline-flex;
  padding: 0.25rem;
}

.company-sort-handle:active {
  cursor: grabbing;
}

.company-name {
  cursor: pointer;
}

.company-accounts {
  gap: 0.35rem;
  margin-top: 0.4rem;
}

.company-account-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.company-account-chip-icon {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
  object-fit: cover;
  font-size: 0.7em;
}

.companies-table tbody tr.ghost {
  opacity: 0.4;
}

.companies-table tbody tr.is-editing {
  background: var(--background-color-editable, rgba(0, 0, 0, 0.03));
}
</style>

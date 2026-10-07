<template>
  <div class="scenarios-table-wrap custom-px-24 custom-ml-4 custom-px-12-mobile">
    <div class="flexbox middle space-12 custom-mb-16">
      <ColorPicker
        v-if="editingId === null"
        v-model="newColor"
      />
      <span
        v-else
        class="scenario-color"
        :style="{ backgroundColor: newColor }"
      />
      <input
        v-model="newName"
        class="bold"
        type="text"
        :placeholder="$t('addScenario')"
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
      v-if="!isLoading && localScenarios.length"
      class="bigdata scenarios-table"
      :class="{ loading: isLoading }"
    >
      <thead>
        <tr>
          <th class="handle-cell" />
          <th class="color-cell">
            {{ $t('color') }}
          </th>
          <th>{{ $t('scenarioName') }}</th>
          <th class="actions-cell" />
        </tr>
      </thead>
      <draggable
        :list="localScenarios"
        tag="tbody"
        handle=".scenario-sort-handle"
        ghost-class="ghost"
        :disabled="isBusy || editingId !== null"
        @end="onSortEnd"
      >
        <tr
          v-for="scenario in localScenarios"
          :key="scenario.id"
          :class="{ 'is-editing': editingId === scenario.id }"
        >
          <td class="handle-cell">
            <span class="scenario-sort-handle">
              <i class="fas fa-grip-vertical" />
            </span>
          </td>
          <td
            class="color-cell"
            @mousedown="onColorMouseDown"
          >
            <ColorPicker
              v-if="editingId === scenario.id"
              v-model="editColor"
            />
            <span
              v-else
              class="scenario-color"
              :style="{ backgroundColor: scenario.color }"
            />
          </td>
          <td>
            <input
              v-if="editingId === scenario.id"
              ref="editInput"
              v-model="editName"
              class="bold full-width"
              type="text"
              :disabled="isBusy"
              @keyup.enter="saveEdit(scenario)"
              @keyup.esc="cancelEdit"
              @blur="onEditBlur(scenario)"
            >
            <span
              v-else
              class="scenario-name"
              @click="startEdit(scenario)"
            >{{ scenario.name }}</span>
          </td>
          <td class="actions-cell">
            <div class="flexbox middle space-8">
              <button
                v-if="editingId !== scenario.id"
                class="button light-gray small"
                :disabled="isBusy"
                :title="$t('update')"
                @click="startEdit(scenario)"
              >
                <i class="fas fa-pencil-alt" />
              </button>
              <button
                class="button light-gray small"
                :disabled="isBusy"
                :title="$t('delete')"
                @click="onDelete(scenario)"
              >
                <i class="fas fa-trash-alt" />
              </button>
            </div>
          </td>
        </tr>
      </draggable>
    </table>

    <p
      v-if="!isLoading && !localScenarios.length"
      class="gray custom-mt-16"
    >
      {{ $t('noScenarios') }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import draggable from 'vuedraggable'
import { useStore } from '@/composables/useStore'
import { i18n } from '@/plugins/locale'
import ColorPicker from '@/components/Inputs/ColorPicker.vue'

const DEFAULT_COLOR = '#1a9afe'

const store = useStore()

const newName = ref('')
const newColor = ref(DEFAULT_COLOR)
const localScenarios = ref([])
const editingId = ref(null)
const editName = ref('')
const editColor = ref(DEFAULT_COLOR)
const editInput = ref(null)
const isLoading = ref(true)
const isBusy = ref(false)
const oldOrder = ref([])
const skipBlurSave = ref(false)

const sortedScenarios = computed(() => store.getters['scenario/sortedScenarios'])

watch(sortedScenarios, (scenarios) => {
  localScenarios.value = scenarios.map(s => ({ ...s }))
  if (!oldOrder.value.length) {
    oldOrder.value = scenarios.map(s => s.id)
  }
}, { immediate: true })

const startEdit = async (scenario) => {
  if (isBusy.value) return
  editingId.value = scenario.id
  editName.value = scenario.name
  editColor.value = scenario.color || DEFAULT_COLOR
  await nextTick()
  const input = Array.isArray(editInput.value) ? editInput.value[0] : editInput.value
  input?.focus()
  input?.select()
}

const cancelEdit = () => {
  editingId.value = null
  editName.value = ''
  editColor.value = DEFAULT_COLOR
}

const saveEdit = async (scenario) => {
  if (isBusy.value || editingId.value !== scenario.id) return

  const name = editName.value.trim()
  const color = editColor.value || DEFAULT_COLOR
  if (!name) {
    cancelEdit()
    return
  }
  if (name === scenario.name && color === (scenario.color || DEFAULT_COLOR)) {
    cancelEdit()
    return
  }

  skipBlurSave.value = true
  isBusy.value = true
  try {
    await store.dispatch('scenario/update', { id: scenario.id, name, color })
    cancelEdit()
  } finally {
    isBusy.value = false
    skipBlurSave.value = false
  }
}

const onEditBlur = (scenario) => {
  if (skipBlurSave.value) return
  saveEdit(scenario)
}

const onColorMouseDown = (event) => {
  if (editingId.value !== null) {
    event.preventDefault()
  }
}

const onCreate = async () => {
  const name = newName.value.trim()
  if (!name || isBusy.value) return

  isBusy.value = true
  try {
    const result = await store.dispatch('scenario/create', {
      name,
      color: newColor.value || DEFAULT_COLOR
    })
    if (result) {
      newName.value = ''
      newColor.value = DEFAULT_COLOR
      oldOrder.value = sortedScenarios.value.map(s => s.id)
    }
  } finally {
    isBusy.value = false
  }
}

const onDelete = async (scenario) => {
  if (isBusy.value) return
  if (!confirm(i18n.t('deleteWarning.scenario'))) return

  isBusy.value = true
  try {
    await store.dispatch('scenario/delete', scenario.id)
    oldOrder.value = sortedScenarios.value.map(s => s.id)
    if (editingId.value === scenario.id) {
      cancelEdit()
    }
  } finally {
    isBusy.value = false
  }
}

const onSortEnd = async () => {
  const newOrder = localScenarios.value.map(s => s.id)
  if (!newOrder.length || JSON.stringify(newOrder) === JSON.stringify(oldOrder.value)) {
    return
  }

  isBusy.value = true
  try {
    await store.dispatch('scenario/sort', {
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
  await store.dispatch('scenario/getList')
  oldOrder.value = sortedScenarios.value.map(s => s.id)
  isLoading.value = false
})
</script>

<style scoped>
.scenarios-table-wrap {
  max-width: 800px;
}

.scenarios-table .handle-cell {
  width: 2rem;
}

.scenarios-table .color-cell {
  width: 4rem;
}

.scenarios-table .actions-cell {
  width: 6rem;
  text-align: right;
}

.scenario-sort-handle {
  cursor: grab;
  color: var(--gray);
  user-select: none;
  display: inline-flex;
  padding: 0.25rem;
}

.scenario-sort-handle:active {
  cursor: grabbing;
}

.scenario-name {
  cursor: pointer;
}

.scenario-color {
  display: inline-block;
  width: 30px;
  height: 30px;
  border-radius: 0.25rem;
  border: 2px solid rgba(0, 0, 0, 0.2);
}

.scenarios-table tbody tr.ghost {
  opacity: 0.4;
}

.scenarios-table tbody tr.is-editing {
  background: var(--background-color-editable, rgba(0, 0, 0, 0.03));
}
</style>

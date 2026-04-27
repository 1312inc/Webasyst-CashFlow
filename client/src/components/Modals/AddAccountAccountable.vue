<template>
  <div
    class="wa-select solid"
    style="margin-top: 12px;"
  >
    <select
      v-model="accountableContactIdModel"
      :disabled="disabled || contacts.length === 0"
    >
      <option
        value="0"
      >
        {{ $t("selectUser") }}
      </option>
      <option
        v-for="contact in contacts"
        :key="contact.id"
        :value="contact.id"
      >
        {{ contact.name }}
      </option>
    </select>
  </div>
</template>

<script setup>

import { computed, ref, watch } from 'vue'

const props = defineProps({
  accountableContactId: {
    type: [String, Number],
    default: '0'
  },
  disabled: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:accountableContactId'])

const contacts = ref([])
const accountableContactIdModel = computed({
  get: () => props.accountableContactId,
  set: (value) => emit('update:accountableContactId', value)
})

watch(() => props.disabled, async (isDisabled) => {
  if (isDisabled) {
    contacts.value = []
    emit('update:accountableContactId', '0')
    return
  }

  const { default: api } = await import('@/plugins/api')

  try {
    const { data } = await api.get('cash.contact.search?is_user=1')

    if (data && Array.isArray(data)) {
      contacts.value = data
    }
  } catch (e) {
    console.error('Ошибка при получении контактов:', e)
  }
}, { immediate: true })

</script>

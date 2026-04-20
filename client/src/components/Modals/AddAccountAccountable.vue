<template>
  <label>
    <slot />
    Подотчет
    <span class="hint">фывфыв</span>

    <div
      class="wa-select solid"
      style="margin-top: 12px;"
    >
      <select
        :value="accountableContactId"
        @change="$emit('update:accountableContactId', $event.target.value)"
      >
        <option
          value=""
          disabled
        >Выберите пользователя</option>
        <option
          v-for="contact in contacts"
          :key="contact.id"
          :value="contact.id"
        >{{ contact.name }}</option>
      </select>
    </div>

  </label>
</template>

<script setup>

import { ref, onMounted } from 'vue'

defineProps({
  accountableContactId: {
    type: Number,
    required: true
  }
})

const contacts = ref([])

onMounted(async () => {
  const { default: api } = await import('@/plugins/api')

  try {
    const res = await api.get('cash.contact.search?is_user=1')
    if (res && Array.isArray(res.items)) {
      contacts.value = res.items
    }
  } catch (e) {
    console.error('Ошибка при получении контактов:', e)
  }
})

</script>

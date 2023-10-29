<script setup>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import AddTransactionBulk from '@/components/Modals/AddTransactionBulk'
import TransactionMove from '@/components/Modals/TransactionMove'
import { ref, nextTick } from 'vue'

const open = ref(false)
const openMove = ref(false)
const openAddBulk = ref(false)
const categoryType = ref('')

async function reOpen () {
  open.value = false
  await nextTick()
  open.value = true
}
</script>

<template>
  <portal>
    <Modal
      v-if="open"
      @close="open = false"
      @reOpen="reOpen"
    >
      <AddTransaction :default-category-type="categoryType" />
    </Modal>

    <Modal
      v-if="openMove"
      @close="openMove = false"
    >
      <TransactionMove />
    </Modal>

    <Modal
      v-if="openAddBulk"
      @close="openAddBulk = false"
    >
      <AddTransactionBulk />
    </Modal>
  </portal>
</template>

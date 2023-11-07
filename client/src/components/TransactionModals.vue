<script setup>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import AddTransactionBulk from '@/components/Modals/AddTransactionBulk'
import TransactionMove from '@/components/Modals/TransactionMove'
import { emitter } from '@/utils/eventBus'
import { ref, nextTick } from 'vue'

const open = ref(false)
const openMove = ref(false)
const openAddBulk = ref(false)

const addTransactionOpts = ref({
  transactionToEdit: undefined,
  defaultDate: undefined
})

async function reOpen () {
  open.value = false
  await nextTick()
  open.value = true
}

emitter.on('openAddTransactionModal', (opts) => {
  addTransactionOpts.value = {}
  if (opts.transaction) {
    addTransactionOpts.value.transactionToEdit = opts.transaction
  } else if (opts.defaultDate) {
    addTransactionOpts.value.defaultDate = opts.defaultDate
  }
  open.value = true
})

</script>

<template>
  <portal>
    <div
      v-if="$helper.isDesktopEnv"
      class="c-fab-button mobile-only"
    >
      <button
        class="circle green"
        style="font-size: 1.6rem;"
        @click="open = true"
      >
        <i class="fas fa-plus" />
      </button>
    </div>

    <Modal
      v-if="open"
      @close="open = false"
      @reOpen="reOpen"
    >
      <AddTransaction
        :transaction="addTransactionOpts.transactionToEdit"
        :default-date="addTransactionOpts.defaultDate"
        default-category-type="expense"
      />
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

<style>
.c-fab-button {
  position: fixed;
  bottom: 1.4rem;
  right: 1.4rem;
}
</style>

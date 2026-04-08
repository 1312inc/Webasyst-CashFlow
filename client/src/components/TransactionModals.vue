<script setup>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import AddTransactionBulk from '@/components/Modals/AddTransactionBulk'
import TransactionMove from '@/components/Modals/TransactionMove'
import { emitter } from '@/plugins/eventBus'
import { useRoute } from 'vue-router/composables'
import { ref, nextTick } from 'vue'

const route = useRoute()

const open = ref(false)
const addTransactionOpts = ref({})

async function reOpen () {
  open.value = false
  await nextTick()
  open.value = true
}

emitter.on('openAddTransactionModal', (opts = {}) => {
  addTransactionOpts.value = opts
  open.value = true
})

function onClick () {
  emitter.emit('openAddTransactionModal', route.name === 'Date' ? { defaultDate: route.params.date } : undefined)
}

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
        @click="onClick"
      >
        <i class="fas fa-plus" />
      </button>
    </div>

    <Modal
      v-if="open"
      @close="open = false"
      @reOpen="reOpen"
    >
      <TransactionMove v-if="addTransactionOpts.type === 'move'" />

      <AddTransactionBulk v-else-if="addTransactionOpts.type === 'addMany'" />

      <AddTransaction
        v-else
        :transaction="addTransactionOpts.transaction"
        :default-date="addTransactionOpts.defaultDate"
        :default-category-type="addTransactionOpts.type ?? 'expense'"
      />
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

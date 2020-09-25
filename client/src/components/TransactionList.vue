<template>
  <div>
    <div class="flex">
      <div>
        <button @click="addTransaction('income')" class="button">Добавить приход</button>
      </div>
      <div>
        <button @click="addTransaction('expense')" class="button">Добавить расход</button>
      </div>
    </div>
    <table class="table-auto w-full">
      <thead>
        <tr>
          <th class="px-4 py-2">Дата</th>
          <th class="px-4 py-2">Сумма</th>
          <th class="px-4 py-2">Категория</th>
          <th class="px-4 py-2">Описание</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody>
        <TransactionListRow
          v-for="transaction in listItems"
          :key="transaction.id"
          :transaction="transaction"
        />
      </tbody>
    </table>
    <Modal v-if="open" @close="open = false">
        <AddTransaction :defaultCategoryType="categoryType" />
    </Modal>
  </div>
</template>

<script>
import TransactionListRow from '@/components/TransactionListRow'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'

export default {
  props: {
    listItems: {
      type: Array
    }
  },

  data () {
    return {
      open: false,
      categoryType: ''
    }
  },

  components: {
    TransactionListRow,
    Modal,
    AddTransaction
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    }
  }
}
</script>

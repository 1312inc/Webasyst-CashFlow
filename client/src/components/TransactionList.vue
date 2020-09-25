<template>
  <div>
    <div class="flex my-6">
      <div class="mr-4">
        <button @click="addTransaction('income')" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white text-base bg-green-600 hover:bg-green-500 focus:outline-none focus:shadow-outline-green focus:border-green-700 active:bg-green-700 transition duration-150 ease-in-out">
          Добавить приход
        </button>
      </div>
      <div>
        <button @click="addTransaction('expense')" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white text-base bg-red-600 hover:bg-red-500 focus:outline-none focus:shadow-outline-red focus:border-red-700 active:bg-red-700 transition duration-150 ease-in-out">
          Добавить расход
        </button>
      </div>
    </div>

    <TransactionListRow
          v-for="transaction in listItems"
          :key="transaction.id"
          :transaction="transaction"
        />

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

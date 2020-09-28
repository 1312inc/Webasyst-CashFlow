<template>
  <div>
    <div class="flex my-6 justify-between mb-6">
      <div v-if="checkedRows.length">
        <button
          type="button"
          class="inline-flex items-center px-4 py-2 border border-solid border-transparent border border-solid leading-5 font-medium rounded-md text-white text-base bg-green-600 hover:bg-green-500 focus:outline-none focus:shadow-outline-green focus:border-green-700 active:bg-green-700 transition duration-150 ease-in-out"
        >
          Удалить
        </button>
      </div>

      <div v-else class="flex">
        <div class="mr-4">
          <button
            @click="addTransaction('income')"
            type="button"
            class="inline-flex items-center px-4 py-2 border border-solid border-transparent border border-solid leading-5 font-medium rounded-md text-white text-base bg-green-600 hover:bg-green-500 focus:outline-none focus:shadow-outline-green focus:border-green-700 active:bg-green-700 transition duration-150 ease-in-out"
          >
            Добавить приход
          </button>
        </div>
        <div>
          <button
            @click="addTransaction('expense')"
            type="button"
            class="inline-flex items-center px-4 py-2 border border-solid border-transparent border border-solid leading-5 font-medium rounded-md text-white text-base bg-red-600 hover:bg-red-500 focus:outline-none focus:shadow-outline-red focus:border-red-700 active:bg-red-700 transition duration-150 ease-in-out"
          >
            Добавить расход
          </button>
        </div>
      </div>

      <div>
        <nav class="relative z-0 inline-flex shadow-sm">
          <a
            href="#"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150"
            aria-label="Previous"
          >
            <!-- Heroicon name: chevron-left -->
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </a>
          <a
            href="#"
            class="-ml-px relative inline-flex items-center px-4 py-2 border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
          >
            1
          </a>
          <a
            href="#"
            class="-ml-px relative inline-flex items-center px-4 py-2 border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
          >
            2
          </a>
          <span
            class="-ml-px relative inline-flex items-center px-4 py-2 border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-700"
          >
     ***REMOVED***
          </span>
          <a
            href="#"
            class="-ml-px relative inline-flex items-center px-4 py-2 border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
          >
            9
          </a>
          <a
            href="#"
            class="-ml-px relative inline-flex items-center px-4 py-2 border border-solid border-gray-300 bg-white leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
          >
            10
          </a>
          <a
            href="#"
            class="-ml-px relative inline-flex items-center px-2 py-2 rounded-r-md border border-solid border-gray-300 bg-white border border-solid leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150"
            aria-label="Next"
          >
            <!-- Heroicon name: chevron-right -->
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </a>
        </nav>
      </div>
    </div>

    <div
      class="flex justify-between border-solid border-b border-gray-500 pb-4 mb-6"
    >
      <div class="inline-flex items-center">
        <input type="checkbox" class="mr-2" @click="checkAll" />
        Последние 30 дней, {{ listItems.length }} транзакций
      </div>
      <div>Export</div>
    </div>

    <TransactionListRow
      v-for="transaction in listItems"
      :key="transaction.id"
      :transaction="transaction"
      :is-checked="checkedRows.includes(transaction.id)"
      @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
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
      categoryType: '',
      checkedRows: []
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
    },

    checkAll ({ target }) {
      this.checkedRows = target.checked ? this.listItems.map((r) => r.id) : []
    },

    onTransactionListRowUpdate (id) {
      const index = this.checkedRows.indexOf(id)
      if (index > -1) {
        this.checkedRows.splice(index, 1)
      } else {
        this.checkedRows.push(id)
      }
    }
  }
}
</script>

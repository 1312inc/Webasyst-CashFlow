<template>
  <div>
    <div class="flexbox">
      <div v-if="checkedRows.length" class="wide">
        <button
          class="button red"
        >
          Удалить
        </button>
      </div>

      <div v-else class="flexbox space-1rem wide">
        <div>
          <button
            @click="addTransaction('income')"
            class="button green"
          >
            <i class="fas fa-plus"></i> Добавить приход
          </button>
        </div>
        <div>
          <button
            @click="addTransaction('expense')"
            class="button orange"
          >
            <i class="fas fa-minus"></i> Добавить расход
          </button>
        </div>
      </div>

      <div>
        <ul class="paging">
            <li><a href="javascript:void(0);">←</a></li>
            <li><a href="javascript:void(0);">1</a></li>
            <li><a href="javascript:void(0);">2</a></li>
            <li class="selected"><a href="javascript:void(0);">3</a></li>
            <li><span>...</span></li>
            <li><a href="javascript:void(0);">21</a></li>
            <li><a href="javascript:void(0);">→</a></li>
        </ul>
      </div>
    </div>

    <table>
      <tr>
        <th class="min-width">
          <input type="checkbox" @click="checkAll" />
        </th>
        <th colspan="5">
          Последние 30 дней, {{ listItems.length }} транзакций
        </th>
      </tr>
      <TransactionListRow
        v-for="transaction in listItems"
        :key="transaction.id"
        :transaction="transaction"
        :is-checked="checkedRows.includes(transaction.id)"
        @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
      />
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

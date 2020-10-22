<template>
  <div>
    <div v-if="!$store.state.transaction.loading" class="flexbox">
      <div v-if="checkedRows.length" class="wide">
        <button class="button red">Удалить</button>
      </div>

      <div
        v-if="!checkedRows.length && $isDesktopEnv"
        class="flexbox space-1rem wide"
      >
        <div>
          <button @click="addTransaction('income')" class="button green">
            <i class="fas fa-plus"></i> {{ $t("addIncome") }}
          </button>
        </div>
        <div>
          <button @click="addTransaction('expense')" class="button orange">
            <i class="fas fa-minus"></i> {{ $t("addExpense") }}
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

    <div v-if="$store.state.transaction.loading">
      <div class="skeleton">
        <span class="skeleton-custom-box" style="height: 36px"></span>
      </div>
    </div>

    <div v-if="$store.state.transaction.loading">
      <div class="skeleton">
        <table>
          <tr v-for="i in listItems.length || 20" :key="i">
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <transition name="fade-appear">
      <table
        class="small zebra"
        v-if="!$store.state.transaction.loading && listItems.length"
      >
        <tr>
          <th class="min-width tw-border-0 tw-border-b tw-border-solid tw-border-gray-400">
            <input type="checkbox" @click="checkAll" />
          </th>
          <th
            colspan="5"
            class="tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
          >
            {{
              $t("transactionsListCount", { days: 30, count: listItems.length })
            }}
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
    </transition>

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

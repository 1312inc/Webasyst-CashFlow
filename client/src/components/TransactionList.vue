<template>
  <div>

    <div v-if="!loading" class="flexbox custom-mb-16">
      <div v-if="checkedRows.length" class="flexbox space-1rem middle wide">
        <button @click="openMove = true" class="yellow red"><i class="fas fa-arrow-right"></i> {{ $t('move') }} {{ checkedRows.length }}</button>
        <button @click="bulkDelete" class="button red"><i class="fas fa-trash-alt"></i> {{ $t('delete') }} {{ checkedRows.length }}</button>
        <button @click="checkedRows = []" class="button nobutton smaller">{{ $t('unselectAll') }}</button>
      </div>

      <div
        v-if="!checkedRows.length && $helper.isDesktopEnv"
        class="flexbox space-1rem middle wide"
      >
        <div v-if="currentType.type !== 'expense'">
          <button @click="addTransaction('income')" class="button green">
            <i class="fas fa-plus"></i> {{ $t("addIncome") }}
          </button>
        </div>
        <div v-if="currentType.type !== 'income'">
          <button @click="addTransaction('expense')" class="button orange">
            <i class="fas fa-minus"></i> {{ $t("addExpense") }}
          </button>
        </div>
        <div v-if="currentType.type !== 'expense' && currentType.type !== 'income'">
          <button @click="addTransaction('transfer')" class="nobutton">
            <i class="fas fa-arrow-right"></i> {{ $t("transfer") }}
          </button>
        </div>
      </div>

      <div>
        <NumPages />
      </div>
    </div>

    <div v-if="loading">
      <div class="skeleton">
        <span class="skeleton-custom-box" style="height: 36px"></span>
      </div>
    </div>

    <div v-if="loading">
      <div class="skeleton">
        <table>
          <tr v-for="i in transactions.data.length || transactions.limit" :key="i">
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
      <div v-if="!loading">
        <table
          class="small zebra"
          v-if="transactions.data.length"
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
                $t("transactionsListCount", { count: transactions.total })
              }}
            </th>
          </tr>
          <TransactionListRow
            v-for="transaction in transactions.data"
            :key="transaction.id"
            :transaction="transaction"
            :is-checked="checkedRows.includes(transaction.id)"
            @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
          />
        </table>
        <div v-else class="tw-text-center custom-py-20">
          {{ $t('emptyList') }}
        </div>
      </div>
    </transition>

    <Modal v-if="open" @close="open = false">
      <AddTransaction :defaultCategoryType="categoryType" />
    </Modal>

    <Modal v-if="openMove" @close="openMove = false">
      <TransactionMove :ids="checkedRows" @success="checkedRows = []" />
    </Modal>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import TransactionListRow from '@/components/TransactionListRow'
import Modal from '@/components/Modal'
import NumPages from '@/components/NumPages'
import AddTransaction from '@/components/AddTransaction'
import TransactionMove from '@/components/TransactionMove'

export default {
  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false,
      checkedRows: []
    }
  },

  components: {
    TransactionListRow,
    Modal,
    NumPages,
    AddTransaction,
    TransactionMove
  },

  computed: {
    ...mapState('transaction', ['transactions', 'loading']),
    ...mapGetters({
      currentType: 'getCurrentType'
    })
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    },

    checkAll ({ target }) {
      this.checkedRows = target.checked ? this.transactions.data.map((r) => r.id) : []
    },

    onTransactionListRowUpdate (id) {
      const index = this.checkedRows.indexOf(id)
      if (index > -1) {
        this.checkedRows.splice(index, 1)
      } else {
        this.checkedRows.push(id)
      }
    },

    bulkDelete () {
      if (confirm(this.$t('bulkDeleteWarning'))) {
        this.$store.dispatch('transaction/bulkDelete', this.checkedRows)
          .then(() => {
            this.checkedRows = []
          })
          .catch(() => {})
      }
    }
  }
}
</script>

<template>
  <div data-sticky-container>
    <div v-if="$helper.isDesktopEnv" class="custom-mb-24" style="height: 61px">
      <div
        class="c-sticky-element custom-py-12 flexbox middle space-1rem"
        data-sticky-class="c-sticky-element--fixed"
      >
        <!-- <div v-if="checkedRows.length" class="flexbox space-1rem middle wide">
        <button @click="openMove = true" class="yellow red"><i class="fas fa-arrow-right"></i> {{ $t('move') }} {{ checkedRows.length }}</button>
        <button @click="bulkDelete" class="button red"><i class="fas fa-trash-alt"></i> {{ $t('delete') }} {{ checkedRows.length }}</button>
        <button @click="checkedRows = []" class="button nobutton smaller">{{ $t('unselectAll') }}</button>
      </div> -->

        <div
          v-if="!checkedRows.length && currentType"
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
          <div
            v-if="
              currentType.type !== 'expense' &&
              currentType.type !== 'income' &&
              $permissions.canAccessTransfers
            "
          >
            <button @click="addTransaction('transfer')" class="nobutton">
              <i class="fas fa-arrow-right"></i> {{ $t("transfer") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- <div v-if="loading">
      <div class="skeleton">
        <span class="skeleton-custom-box" style="height: 36px"></span>
      </div>
    </div> -->

    <TransactionListUpcoming class="custom-mb-24" />
    <TransactionListIncoming />

    <Modal v-if="open" @close="open = false">
      <AddTransaction :defaultCategoryType="categoryType" />
    </Modal>

    <Modal v-if="openMove" @close="openMove = false">
      <TransactionMove :ids="checkedRows" @success="checkedRows = []" />
    </Modal>
  </div>
</template>

<script>
// import Sticky from 'sticky-js'
import { mapState, mapGetters } from 'vuex'
import TransactionListIncoming from '@/components/TransactionListIncoming'
import TransactionListUpcoming from '@/components/TransactionListUpcoming'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'
import TransactionMove from '@/components/TransactionMove'

export default {
  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false,
      checkedRows: [],
      featurePeriod: 7,
      upcomingBlockOpened: false
    }
  },

  components: {
    TransactionListIncoming,
    TransactionListUpcoming,
    Modal,
    AddTransaction,
    TransactionMove
  },

  computed: {
    ...mapState('transaction', [
      'transactions',
      'queryParams',
      'loading',
      'detailsInterval'
    ]),
    ...mapGetters({
      currentType: 'getCurrentType'
    }),

    isTransactionsExists () {
      return (
        this.sortedTransactions.incoming.length ||
        this.sortedTransactions.upcoming.length
      )
    }
  },

  mounted () {
    // if (document.querySelector('.c-sticky-element')) {
    //   this.sticky = new Sticky('.c-sticky-element')
    // }
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    },

    checkAll ({ target }) {
      this.checkedRows = target.checked
        ? this.transactions.data.map(r => r.id)
        : []
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
        this.$store
          .dispatch('transaction/bulkDelete', this.checkedRows)
          .then(() => {
            this.checkedRows = []
          })
          .catch(() => {})
      }
    }
  }
}
</script>

<style>
.c-sticky-element {
  background-color: #fff;
  border-bottom: 1px solid #fff;
  z-index: 50;
}
.c-sticky-element--fixed {
  border-bottom: 1px solid #eee;
}
</style>

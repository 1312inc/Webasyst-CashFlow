<template>
<div>
    <div v-if="$helper.isDesktopEnv" style="height: 61px">
      <div
        class="c-sticky-element custom-py-12 flexbox middle space-1rem"
        data-sticky-class="c-sticky-element--fixed"
        data-margin-top=64
      >
        <div v-if="checkedRows.length" class="flexbox space-1rem middle wide">
          <button @click="openMove = true" class="yellow red">
            <i class="fas fa-arrow-right"></i> {{ $t("move") }}
            {{ checkedRows.length }}
          </button>
          <button @click="bulkDelete" class="button red">
            <i class="fas fa-trash-alt"></i> {{ $t("delete") }}
            {{ checkedRows.length }}
          </button>
          <button @click="unselectAll" class="button nobutton smaller">
            {{ $t("unselectAll") }}
          </button>
        </div>

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

    <Modal v-if="open" @close="open = false">
      <AddTransaction :defaultCategoryType="categoryType" />
    </Modal>

    <Modal v-if="openMove" @close="openMove = false">
      <TransactionMove />
    </Modal>

</div>
</template>

<script>
// import Sticky from 'sticky-js'
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'
import TransactionMove from '@/components/TransactionMove'
export default {
  components: {
    Modal,
    AddTransaction,
    TransactionMove
  },

  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false
    }
  },

  computed: {
    ...mapGetters({
      currentType: 'getCurrentType'
    }),

    checkedRows () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
    }
  },

  // mounted () {
  // if (document.querySelector('.c-sticky-element')) {
  //   this.sticky = new Sticky('.c-sticky-element')
  // }
  // },

  beforeDestroy () {
    this.$store.commit('transactionBulk/empty')
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    },

    bulkDelete () {
      if (confirm(this.$t('bulkDeleteWarning'))) {
        this.$store.dispatch('transactionBulk/bulkDelete')
      }
    },

    unselectAll () {
      this.$store.commit('transactionBulk/empty')
    }
  }
}
</script>

<style>
.c-sticky-element {
  background-color: #fff;
  border-bottom: 1px solid #fff;
  z-index: 10;
}
.c-sticky-element--fixed {
  border-bottom: 1px solid #eee;
}
</style>

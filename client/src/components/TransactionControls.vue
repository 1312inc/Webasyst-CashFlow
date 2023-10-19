<template>
  <div>
    <div
      v-if="checkedRows.length && multiselectView"
      class="flexbox space-12 middle custom-py-12"
      :class="direction === 'column' && 'vertical'"
    >
      <button
        class="button blue"
        :class="direction === 'column' && 'custom-mb-12'"
        @click="openMove = true"
      >
        <i class="fas fa-coins" /> {{ $t("move") }} ({{
          checkedRows.length
        }})
      </button>
      <button
        class="button red"
        :class="direction === 'column' && 'custom-mb-12'"
        @click="bulkDelete"
      >
        <i class="fas fa-trash-alt" /> {{ $t("delete") }} ({{
          checkedRows.length
        }})
      </button>
      <button
        class="button nobutton smaller"
        @click="unselectAll"
      >
        {{ $t("unselectAll") }}
      </button>
    </div>

    <div
      v-if="$helper.isDesktopEnv && currentType"
      ref="controlButtons"
      class="flexbox wrap space-12 middle custom-pt-12"
    >
      <div
        v-show="currentType.type !== 'expense'"
        class="custom-pb-12"
      >
        <button
          class="button c-button-add-income nowrap"
          @click="addTransaction('income')"
        >
          <i class="fas fa-plus" />
          <span class="custom-ml-8">{{ $t("addIncome") }}</span>
        </button>
      </div>
      <div
        v-show="currentType.type !== 'income'"
        class="custom-pb-12"
      >
        <button
          class="button c-button-add-expense nowrap"
          @click="addTransaction('expense')"
        >
          <i class="fas fa-minus" />
          <span class="custom-ml-8">{{ $t("addExpense") }}</span>
        </button>
      </div>
      <div
        v-if="
          currentType.type !== 'expense' &&
            currentType.type !== 'income' &&
            $permissions.canAccessTransfers &&
            $store.state.account.accounts.length > 1
        "
        class="custom-pb-12"
      >
        <button
          class="button light-gray nowrap"
          @click="addTransaction('transfer')"
        >
          <span>
            <i class="fas fa-exchange-alt" />
            <span class="desktop-only custom-ml-8">{{ $t("transfer") }}</span>
          </span>
        </button>
      </div>
      <div class="custom-pb-12">
        <button
          class="button nobutton gray nowrap"
          @click="openAddBulk = true"
        >
          <span>
            <i class="fas fa-list-ul" />
            <span class="desktop-only custom-ml-8 black">{{ $t("addMany") }}</span>
          </span>
        </button>
      </div>
    </div>

    <portal>
      <Modal
        v-if="open"
        @close="open = false"
        @reOpen="reOpen"
      >
        <AddTransaction :default-category-type="categoryType" />
      </Modal>
    </portal>

    <portal>
      <Modal
        v-if="openMove"
        @close="openMove = false"
      >
        <TransactionMove />
      </Modal>
    </portal>

    <portal>
      <Modal
        v-if="openAddBulk"
        @close="openAddBulk = false"
      >
        <AddTransactionBulk />
      </Modal>
    </portal>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import AddTransactionBulk from '@/components/Modals/AddTransactionBulk'
import TransactionMove from '@/components/Modals/TransactionMove'
export default {

  components: {
    Modal,
    AddTransaction,
    AddTransactionBulk,
    TransactionMove
  },
  props: {
    direction: {
      type: String
    },
    multiselectView: {
      type: Boolean,
      default: true
    }
  },

  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false,
      openAddBulk: false
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

  watch: {
    // add pulsar effect on the button if first time navigate
    async '$route' (to) {
      await this.$nextTick()
      const pulsarButton = this.$refs.controlButtons.querySelector('button')
      if (pulsarButton) {
        pulsarButton.classList.remove('pulsar')
        if (to.params.isFirtsTimeNavigate) {
          setTimeout(() => {
            pulsarButton.classList.add('pulsar')
          }, 1500)
        }
      }
    }
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
      this.$store.commit('transactionBulk/emptySelectedTransactionsIds')
    },

    async reOpen () {
      this.open = false
      await this.$nextTick()
      this.open = true
    }
  }
}
</script>

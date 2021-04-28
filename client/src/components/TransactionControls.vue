<template>
  <div>
    <div
      v-if="checkedRows.length && multiselectView"
      class="flexbox space-12 middle custom-py-12"
      :class="direction === 'column' && 'vertical'"
    >
      <button
        @click="openMove = true"
        class="button blue"
        :class="direction === 'column' && 'custom-mb-12'"
      >
        <i class="fas fa-arrow-right"></i> {{ $t("move") }} ({{
          checkedRows.length
        }})
      </button>
      <button
        @click="bulkDelete"
        class="button red"
        :class="direction === 'column' && 'custom-mb-12'"
      >
        <i class="fas fa-trash-alt"></i> {{ $t("delete") }} ({{
          checkedRows.length
        }})
      </button>
      <button @click="unselectAll" class="button nobutton smaller">
        {{ $t("unselectAll") }}
      </button>
    </div>

    <div
      v-if="$helper.isDesktopEnv && currentType"
      ref="controlButtons"
      class="flexbox wrap-mobile space-12 middle custom-py-12"
    >
      <div v-show="currentType.type !== 'expense'">
        <button @click="addTransaction('income')" class="button c-button-add-income">
          <i class="fas fa-plus"></i>
          <span class="custom-ml-8">{{ $t("addIncome") }}</span>
        </button>
      </div>
      <div v-show="currentType.type !== 'income'">
        <button @click="addTransaction('expense')" class="button c-button-add-expense">
          <i class="fas fa-minus"></i>
          <span class="custom-ml-8">{{ $t("addExpense") }}</span>
        </button>
      </div>
      <div
        v-if="
          currentType.type !== 'expense' &&
          currentType.type !== 'income' &&
          $permissions.canAccessTransfers
        "
      >
        <button @click="addTransaction('transfer')" class="button light-gray">
          <span>
            <i class="fas fa-exchange-alt"></i>
            <span class="desktop-only custom-ml-8">{{ $t("transfer") }}</span>
          </span>
        </button>
      </div>
    </div>

    <portal>
      <Modal v-if="open" @close="open = false">
        <AddTransaction :defaultCategoryType="categoryType" />
      </Modal>
    </portal>

    <portal>
      <Modal v-if="openMove" @close="openMove = false">
        <TransactionMove />
      </Modal>
    </portal>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import TransactionMove from '@/components/Modals/TransactionMove'
export default {
  props: {
    direction: {
      type: String
    },
    multiselectView: {
      type: Boolean,
      default: true
    }
  },

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
    }
  }
}
</script>

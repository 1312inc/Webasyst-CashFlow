<template>
  <div
    class="c-transaction-controls-sticky"
    :class="{'desktop-and-tablet-only': $helper.isDesktopEnv}"
  >
    <BlankBox>
      <div class="c-transaction-controls">
        <div
          v-if="$store.state.multiSelectMode && multiselectView"
          class="c-transaction-controls-check flexbox middle"
          :class="direction === 'column' && 'vertical'"
        >
          <button
            class="button blue nowrap"
            :disabled="!checkedRows.length"
            :class="direction === 'column' && 'custom-mb-12'"
            @click="openMove = true"
          >
            <i class="fas fa-coins" /> {{ $t("move") }} ({{
              checkedRows.length
            }})
          </button>
          <button
            class="button red nowrap"
            :disabled="!checkedRows.length"
            :class="direction === 'column' && 'custom-mb-12'"
            @click="bulkDelete"
          >
            <i class="fas fa-trash-alt" /> {{ $t("delete") }} ({{
              checkedRows.length
            }})
          </button>
          <button
            class="button nobutton smaller nowrap"
            :disabled="!checkedRows.length"
            @click="unselectAll"
          >
            {{ $t("unselectAll") }}
          </button>
        </div>

        <div
          v-if="$helper.isDesktopEnv && currentType"
          ref="controlButtons"
          class="flexbox wrap space-12 middle custom-px-12"
        >
          <div v-show="currentType.type !== 'expense'">
            <button
              class="button c-button-add-income nowrap"
              @click="addTransaction('income')"
            >
              <i class="fas fa-plus" />
              <span class="custom-ml-8">{{ $t("addIncome") }}</span>
            </button>
          </div>
          <div v-show="currentType.type !== 'income'">
            <button
              class="button c-button-add-expense nowrap"
              @click="addTransaction('expense')"
            >
              <i class="fas fa-minus" />
              <span class="custom-ml-8">{{ $t("addExpense") }}</span>
            </button>
          </div>
          <div
            v-if="currentType.type !== 'expense' &&
              currentType.type !== 'income' &&
              $permissions.canAccessTransfers &&
              $store.state.account.accounts.length > 1
            "
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
          <div>
            <button
              class="button nobutton gray nowrap"
              @click="addTransaction('addMany')"
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
            v-if="openMove"
            @close="openMove = false"
          >
            <TransactionMove />
          </Modal>
        </portal>
      </div>
    </BlankBox>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import TransactionMove from '@/components/Modals/TransactionMove'
import BlankBox from './BlankBox.vue'

export default {

  components: {
    Modal,
    TransactionMove,
    BlankBox
  },

  props: {
    direction: {
      type: String
    },
    multiselectView: {
      type: Boolean,
      default: true
    },
    defaultDate: {
      type: String
    }
  },

  data () {
    return {
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

  mounted () {
    const addTransactionQuery = this.$route.query.addtransaction
    if (['income', 'expense'].includes(addTransactionQuery)) {
      this.addTransaction(addTransactionQuery)
    }
  },

  methods: {
    addTransaction (type) {
      this.$eventBus.emit('openAddTransactionModal', {
        type,
        defaultDate: this.defaultDate
      })
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

<style>
.c-transaction-controls-sticky {
  position: sticky;
  top: 4rem;
  z-index: 999;
}

.c-mobile-build .c-transaction-controls-sticky {
  top: 0;
}

.c-transaction-controls {
  display: flex;
  align-items: center;
  height: 60px;
}

/* .c-transaction-controls:empty {
  display: none;
} */

@media screen and (min-width: 760px) {
  .c-transaction-controls-check {
    display: none;
  }
}
</style>

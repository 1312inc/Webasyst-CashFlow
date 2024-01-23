<template>
  <li
    ref="reference"
    class="item c-item"
    :class="classes"
    :style="isRepeatingGroup && 'cursor: initial;'"
    @mouseover="() => { if (isCompactMode) { openFloating = true } }"
    @mouseleave="() => { if (isCompactMode) { openFloating = false } }"
  >
    <div
      v-if="showDate && !isCompactMode"
      class="mobile-only custom-py-8"
    >
      <strong>
        {{
          $moment(transaction.date).format(
            $moment.locale() === "ru" ? "D MMMM" : "MMMM D"
          )
        }}
      </strong>
      <span class="hint">{{ $moment(transaction.date).format("dddd") }}</span>
    </div>

    <div
      class="flexbox middle space-12"
      @mouseover="isHover = true"
      @mouseleave="isHover = false"
      @click="handleClick"
    >
      <div
        v-if="$helper.showMultiSelect() && !isCompactMode"
        class="custom-my-4"
        :class="{ 'desktop-only': $helper.isDesktopEnv }"
        style="width: 1rem; height: 1rem;"
      >
        <span
          v-show="isHoverComputed && !isRepeatingGroup"
          class="wa-checkbox"
          @click.stop="checkboxSelect"
        >
          <input
            type="checkbox"
            :checked="isChecked"
          >
          <span>
            <span class="icon">
              <i class="fas fa-check" />
            </span>
          </span>
        </span>
      </div>

      <div
        v-if="!isCompactMode"
        class="desktop-and-tablet-only"
        style="width: 7rem;flex-shrink: 0;"
      >
        <template v-if="showDate">
          <div class="custom-mb-4 bold nowrap c-group-date">
            {{
              $moment(transaction.date).format(
                $moment.locale() === "ru" ? "D MMMM" : "MMMM D"
              )
            }}
          </div>

          <div class="hint">
            {{
              $moment().year() == $moment(transaction.date).year()
                ? daysBefore > 0
                  ? daysBefore === 1
                    ? $t("tomorrow")
                    : $moment(transaction.date).from($helper.currentDate)
                  : $moment(transaction.date).format("dddd")
                : $moment(transaction.date).year()
            }}
          </div>
        </template>
      </div>

      <TransactionListGroupRowGlyph
        :transaction="transaction"
        :category="category"
        :account="account"
        :is-collapse-header="isCollapseHeader"
        :is-repeating-group="isRepeatingGroup"
        :collapse-header-data="collapseHeaderData"
      />
      <div class="wide flexbox middle space-8 c-item-border">
        <div
          v-if="!isCompactMode"
          class="wide"
          style="overflow: hidden"
        >
          <TransactionListGroupRowDesc
            :transaction="transaction"
            :collapse-header-data="collapseHeaderData"
            :is-repeating-group="isRepeatingGroup"
            :category="category"
          />
          <div
            v-if="transaction.description || transaction.contractor_contact"
            class="black small text-ellipsis"
            style="flex-shrink: 1"
          >
            <span v-if="transaction.description">
              {{ transaction.description }}
            </span>
            <span
              v-if="transaction.contractor_contact"
              class="gray"
            >
              {{ transaction.contractor_contact.name }}
            </span>
          </div>
          <span
            v-if="!transaction.contractor_contact && !transaction.description"
            class="gray small"
          >
            {{ $t('noDesc') }}
          </span>
        </div>
        <div class="c-item-amount">
          <div
            :style="`color: ${category.color}`"
            class="bold nowrap custom-mb-4 text-ellipsis"
          >
            {{
              (isCompactMode && !isCollapseHeader) ? `${transaction.amountShorten} ${$helper.currencySignByCode(account.currency)}` :
              $helper.toCurrency({
                value: isCollapseHeader
                  ? collapseHeaderData.totalAmount
                  : transaction.amount,
                currencyCode: account.currency,
                isDynamics: true
              })
            }}
          </div>
          <div
            v-if="account.name && !isCompactMode"
            class="text-ellipsis small gray"
          >
            {{ account.name }}
            <span
              v-if="transaction.balance"
              class="nowrap black"
              :title="$t('accountBalanceTransactionListHint')"
            >
              {{
                $helper.toCurrency({
                  value: transaction.balance,
                  currencyCode: account.currency
                })
              }}
            </span>
          </div>
        </div>
        <div
          v-if="isCompactMode"
          class="hint align-center"
        >
          <span class="black">{{ $moment(transaction.date).toDate().toLocaleDateString($moment.locale(), { month: 'short', day: 'numeric' }) }}</span>
          <br>
          {{ $moment(transaction.date).from($moment().startOf('day')) }}
        </div>
        <transition
          name="fade"
          :duration="300"
        >
          <TransactionListCompleteButton
            v-show="transaction.is_onbadge && !archive"
            :transaction="transaction"
            :is-fixed="isCompactMode"
            :account="account"
            @processEdit="openModal(true)"
          />
        </transition>
      </div>
    </div>

    <portal>
      <Modal
        v-if="open"
        @close="open = false"
      >
        <AddTransaction
          :transaction="transaction"
          :off-onbadge="offBadgeInTransactionModal"
        />
      </Modal>
    </portal>

    <div
      v-if="openFloating"
      ref="floating"
      class="dropdown is-opened"
      style="z-index: 999;"
      :style="floatingStyles"
    >
      <div
        class="dropdown-body"
        style="min-width: 200px;"
      >
        <div
          class="custom-p-8"
          style="display: flex; flex-direction: column; gap: .2rem;"
        >
          <span v-if="transaction.contractor_contact?.name">{{ transaction.contractor_contact.name }}</span>
          <span
            v-if="category"
            :style="`color: ${category.color}`"
            class="bold nowrap small text-ellipsis"
          >{{ category.name }}</span>
          <span class="hint">{{ transaction.description || $t('noDesc') }}</span>
        </div>
      </div>
    </div>
  </li>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import TransactionListCompleteButton from './TransactionListCompleteButton'
import TransactionListGroupRowDesc from './TransactionListGroupRowDesc'
import TransactionListGroupRowGlyph from './TransactionListGroupRowGlyph'
import { useFloating } from '@floating-ui/vue'
import { appState } from '@/utils/appState'

const reference = ref(null)
const floating = ref(null)
const openFloating = ref(false)

const { floatingStyles } = useFloating(reference, floating, {
  placement: 'bottom-start',
  strategy: 'fixed'
})
</script>

<script>

export default {
  components: {
    Modal,
    AddTransaction,
    TransactionListCompleteButton,
    TransactionListGroupRowDesc,
    TransactionListGroupRowGlyph
  },

  inject: {
    archive: { default: false }
  },
  props: {
    transaction: {
      type: Object
    },

    showChecker: {
      type: Boolean,
      default: false
    },

    collapseHeaderData: {
      type: Object,
      default: null
    },

    isRepeatingGroup: {
      type: Boolean,
      default: false
    },

    showDate: {
      type: Boolean,
      default: true
    },

    visibleSelectCheckbox: {
      type: Boolean,
      default: false
    },

    isCompactMode: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      open: false,
      isHover: false,
      offBadgeInTransactionModal: false
    }
  },

  computed: {
    account () {
      return (
        this.$store.getters['account/getById'](this.transaction.account_id) ||
    { }
      )
    },

    category () {
      return this.$store.getters['category/getById'](
        this.transaction.category_id
      )
    },

    isCollapseHeader () {
      return !!this.collapseHeaderData
    },

    isChecked () {
      return this.$store.getters['transactionBulk/isSelected'](
        this.transaction.id
      )
    },

    isHoverComputed () {
      if (this.$isSpaMobileMode || this.visibleSelectCheckbox) {
        return true
      }
      return this.showChecker ? true : this.isHover
    },

    isOverdue () {
      return (
        this.transaction.date < this.$helper.currentDate &&
    this.transaction.is_onbadge
      )
    },

    isToday () {
      return (
        this.transaction.date === this.$helper.currentDate &&
    this.transaction.is_onbadge
      )
    },

    daysBefore () {
      return this.$moment(this.transaction.date).diff(
        this.$helper.currentDate,
        'days'
      )
    },

    classes () {
      if (this.archive) {
        return {
          'c-item-archived': true
        }
      }
      return {
        'custom-pb-8': this.isCompactMode,
        'c-transaction-group': this.isCollapseHeader || this.isRepeatingGroup, // styles for the collapsed transactions
        'c-upcoming': this.$moment(this.transaction.date) > this.$moment(), // styles for the upcoming transactions
        'c-item-overdue': this.isOverdue,
        'c-item-red-process': this.isOverdue || this.isToday,
        'c-item-selected': this.isChecked,
        'c-item--updated':
    this.transaction.$_flagUpdated || this.transaction.$_flagCreated
      }
    }
  },

  methods: {
    handleClick (e) {
      if (this.isCollapseHeader) {
        this.$emit('toggleCollapseHeader')
      } else if (this.isRepeatingGroup) {
        e.preventDefault()
      } else if (appState.webView && this.$store.state.multiSelectMode) {
        this.checkboxSelect()
      } else {
        this.openModal()
      }
    },

    openModal (offOnbadge = false) {
      if (this.archive) {
        alert(this.$t('restoreInfo'))
        return
      }
      this.offBadgeInTransactionModal = offOnbadge
      if (this.$isSpaMobileMode) {
      // emitting for the mobile platform
        window.emitter.emit('editTransaction', this.transaction)
      } else {
        this.open = true
      }
    },

    checkboxSelect (event) {
      const method = this.isChecked ? 'unselect' : 'select'
      let ids = [this.transaction.id]
      // if collapsed with multiple transactions
      if (this.isCollapseHeader) {
        ids = [...this.collapseHeaderData.ids]
      }

      const transactions = this.$store.state.transaction.transactions.data
      const lastCheckboxIndex = this.$store.state.transactionBulk
        .lastCheckboxIndex
      const currentIndex = transactions.indexOf(this.transaction)

      // with Shift key selection
      if (event?.shiftKey && lastCheckboxIndex > -1) {
        transactions
          .slice(
            Math.min(currentIndex, lastCheckboxIndex) + 1,
            Math.max(currentIndex, lastCheckboxIndex)
          )
          .forEach(e => ids.push(e.id))
        ids.push(transactions[lastCheckboxIndex].id)
      }

      ids = ids.filter((e, i, a) => a.indexOf(e) === i)

      this.$store.commit(`transactionBulk/${method}`, ids)
      this.$store.commit('transactionBulk/setLastCheckboxIndex', currentIndex)
    }
  }
}
</script>

<style>
@keyframes updated {
  from {
    background-color: var(--highlighted-green);
  }

  to {
    background-color: rgba(255, 255, 255, 0);
  }
}

.c-item--updated {
  animation-name: updated;
  animation-duration: 3s;
  animation-timing-function: linear;
  animation-direction: alternate;
  animation-iteration-count: 1;
}
</style>

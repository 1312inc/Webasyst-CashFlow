<template>
  <li
    class="item c-item"
    ref="row"
    :class="classes"
    :style="isRepeatingGroup && 'cursor: initial;'"
  >
    <div v-if="showDate" class="mobile-only custom-py-8">
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
      @mouseover="isHover = true"
      @mouseleave="isHover = false"
      @click="handleClick"
      class="flexbox middle space-12"
    >
      <div
        v-if="$helper.showMultiSelect()"
        :class="{ 'desktop-only': $helper.isDesktopEnv }"
        style="min-width: 1rem"
      >
        <span
          v-show="isHoverComputed && !isRepeatingGroup"
          @click.stop="checkboxSelect"
          class="wa-checkbox"
        >
          <input type="checkbox" :checked="isChecked" />
          <span>
            <span class="icon">
              <i class="fas fa-check"></i>
            </span>
          </span>
        </span>
      </div>

      <div class="desktop-and-tablet-only" style="width: 7rem;flex-shrink: 0;">
        <template v-if="showDate">
          <div class="custom-mb-4 bold nowrap c-group-date">
            {{
              $moment(transaction.date).format(
                $moment.locale() === "ru" ? "D MMMM" : "MMMM D"
              )
            }}
          </div>
          <span v-if="daysBefore > 0" class="hint">{{
            daysBefore === 1
              ? $t("tomorrow")
              : this.$moment(transaction.date).from($helper.currentDate)
          }}</span>
          <div v-else class="hint">
            {{ $moment(transaction.date).format("dddd") }}
          </div>
        </template>
      </div>

      <TransactionListGroupRowGlyph
        :transaction="transaction"
        :category="category"
        :account="account"
        :isCollapseHeader="isCollapseHeader"
        :isRepeatingGroup="isRepeatingGroup"
        :collapseHeaderData="collapseHeaderData"
      />
      <div class="wide flexbox middle space-8 c-item-border">
        <div class="wide" style="overflow: hidden">
          <TransactionListGroupRowDesc
            :transaction="transaction"
            :collapseHeaderData="collapseHeaderData"
            :isRepeatingGroup="isRepeatingGroup"
            :category="category"
          />
          <div
            v-if="transaction.description"
            class="black small text-ellipsis"
            style="flex-shrink: 1"
          >
            {{ transaction.description }}
          </div>
          <div
            v-if="transaction.contractor_contact && !transaction.description"
            class="gray small text-ellipsis"
            style="flex-shrink: 1"
          >
            {{ transaction.contractor_contact.name }}
          </div>
        </div>
        <div class="c-item-amount">
          <div
            :style="`color: ${category.color}`"
            class="bold nowrap custom-mb-4"
          >
            {{
              $helper.toCurrency({
                value: isCollapseHeader
                  ? collapseHeaderData.totalAmount
                  : transaction.amount,
                currencyCode: account.currency,
                isDynamics: true
              })
            }}
          </div>
          <div v-if="account.name" class="text-ellipsis small gray">
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
        <transition name="fade" :duration="300">
          <TransactionListCompleteButton
            v-show="transaction.is_onbadge"
            @processEdit="openModal(true)"
            :transaction="transaction"
            :account="account"
          />
        </transition>
      </div>
    </div>

    <portal>
      <Modal v-if="open" @close="open = false">
        <AddTransaction
          :transaction="transaction"
          :offOnbadge="offBadgeInTransactionModal"
        />
      </Modal>
    </portal>
  </li>
</template>

<script>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import TransactionListCompleteButton from './TransactionListCompleteButton'
import TransactionListGroupRowDesc from './TransactionListGroupRowDesc'
import TransactionListGroupRowGlyph from './TransactionListGroupRowGlyph'
export default {
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
    }
  },

  data () {
    return {
      open: false,
      isHover: false,
      offBadgeInTransactionModal: false
    }
  },

  components: {
    Modal,
    AddTransaction,
    TransactionListCompleteButton,
    TransactionListGroupRowDesc,
    TransactionListGroupRowGlyph
  },

  computed: {
    account () {
      return (
        this.$store.getters['account/getById'](this.transaction.account_id) ||
        {}
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
      if (process.env.VUE_APP_MODE === 'mobile' || this.visibleSelectCheckbox) {
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
      return {
        'c-transaction-group': this.isCollapseHeader || this.isRepeatingGroup, // styles for the collapsed transactions
        'c-upcoming': this.$moment(this.transaction.date) > this.$moment(), // styles for the upcoming transactions
        'c-item-overdue': this.isOverdue,
        'c-item-red-process': this.isOverdue || this.isToday,
        'c-item-selected': this.isChecked
      }
    }
  },

  watch: {
    transaction (val) {
      if (val.$_flagUpdated) {
        this.$refs.row.addEventListener('animationend', () => {
          this.$refs.row.classList.remove('c-item--updated')
        })
        this.$refs.row.classList.add('c-item--updated')
      }
    }
  },

  methods: {
    handleClick (e) {
      if (this.isCollapseHeader) {
        this.$emit('toggleCollapseHeader')
      } else if (this.isRepeatingGroup) {
        e.preventDefault()
      } else {
        this.openModal()
      }
    },

    openModal (offOnbadge = false) {
      this.offBadgeInTransactionModal = offOnbadge
      if (process.env.VUE_APP_MODE === 'mobile') {
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
      if (event.shiftKey && lastCheckboxIndex > -1) {
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
  animation-fill-mode: forwards;
  animation-timing-function: linear;
  animation-direction: alternate;
  animation-iteration-count: 1;
}
</style>

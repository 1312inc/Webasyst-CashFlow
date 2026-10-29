<template>
  <component
    :is="tableLayout ? 'tr' : 'li'"
    ref="reference"
    class="item c-item"
    :class="classes"
    :style="isRepeatingGroup && 'cursor: initial;'"
    @mouseover="() => { if (isCompactMode) { openFloating = true } }"
    @mouseleave="() => { if (isCompactMode) { openFloating = false } }"
    @click="tableLayout && handleClick($event)"
  >
    <template v-if="tableLayout">
      <td
        class="c-transactions-table__check"
        @click.stop
      >
        <span
          v-show="!isRepeatingGroup"
          class="wa-checkbox c-item-checkbox"
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
      </td>
      <td
        data-copy-role="date"
        :data-copy="dateLabel"
      >
        {{ dateMain }}
        <span
          v-if="dateWeekday"
          class="gray"
        >{{ dateWeekday }}</span>
        <template v-else>
          {{ dateYear }}
        </template>
      </td>
      <td
        data-copy-role="category"
        :data-copy="categoryLabel"
        class="c-transactions-table__category semibold"
      >
        <span class="c-transactions-table__category-line">
          <span
            class="text-ellipsis"
            :style="category && category.color ? `color: ${category.color}` : null"
          >{{ categoryLabel }}</span>
          <span
            v-if="scenario"
            class="badge squared nowrap small"
            :style="{ backgroundColor: scenario.color }"
            :title="scenario.name"
          >
            <span class="text-ellipsis">
              <i class="fas fa-code-branch" />
              {{ scenario.name }}
            </span>
          </span>
        </span>
      </td>
      <td
        data-copy-role="description"
        :data-copy="descriptionText"
        class="c-transactions-table__desc text-ellipsis"
      >
        <span v-if="descriptionText">{{ descriptionText }}</span>
      </td>
      <td
        data-copy-role="amount"
        :data-copy="copyAmount"
        class="c-transactions-table__amount bold nowrap align-right"
        :style="category && category.color ? `color: ${category.color}` : null"
      >
        {{
          $helper.toCurrency({
            value: isCollapseHeader ? collapseHeaderData.totalAmount : transaction.amount,
            currencyCode: account.currency,
            isDynamics: true
          })
        }}
        <i
          v-if="isRepeatingGroup || transaction.repeating_id"
          class="fas fa-redo c-transactions-table__repeat custom-ml-4"
          :title="repeatingTitle"
        />
      </td>
      <td
        data-copy-role="contractor"
        :data-copy="contractorName"
        class="text-ellipsis"
      >
        {{ contractorName }}
      </td>
      <td
        data-copy-role="account"
        :data-copy="account.name || ''"
        class="text-ellipsis"
        :title="account.name"
      >
        {{ account.name }}
      </td>
      <td
        v-if="showBalance"
        data-copy-role="balance"
        :data-copy="copyBalance"
        class="nowrap align-right"
        :title="$t('accountBalanceTransactionListHint')"
      >
        <template v-if="transaction.balance">
          {{
            $helper.toCurrency({
              value: transaction.balance,
              currencyCode: account.currency
            })
          }}
        </template>
      </td>
      <td
        class="c-transactions-table__action"
        @click.stop
      >
        <TransactionListCompleteButton
          v-show="transaction.is_onbadge && !archive"
          :transaction="transaction"
          :account="account"
          :is-fixed="true"
          @processEdit="openModal(true)"
        />
      </td>
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
    </template>
    <template v-else>
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
        @click="handleClick"
      >
        <div
          v-if="!isCompactMode"
          class="c-item-checkbox flex-none"
          :class="{'desktop-and-tablet-only': !$helper.showMultiSelect()}"
        >
          <span
            v-show="!isRepeatingGroup"
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
          class="desktop-and-tablet-only flex-none"
          style="width: 6rem;"
        >
          <template v-if="showDate">
            <div class="custom-mb-4 bold nowrap c-group-date text-ellipsis">
              {{
                $moment(transaction.date).format(
                  $moment.locale() === "ru" ? "D MMM" : "MMM D"
                )
              }}
            </div>

            <div class="hint text-ellipsis">
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

        <div
          v-if="!isCompactMode"
          class="wide c-item-border"
        >
          <div class="wide flexbox full-width middle space-8 custom-mb-8">
            <TransactionListGroupRowDesc
              :transaction="transaction"
              :collapse-header-data="collapseHeaderData"
              :category="category"
            />

            <div
              :style="`color: ${category.color}`"
              class="bold nowrap"
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
            <!-- if repeating transactions in future -->
            <div
              v-if="isRepeatingGroup"
              class="black text-ellipsis"
              :title="
                $t('repeatingTransactionDesc', {
                  repeats: transaction.affected_transactions
                })
              "
            >
              <span class="smaller custom-mr-4">
                <i class="fas fa-redo opacity-50" />
              </span>
            </div>

            <div
              v-else-if="transaction.repeating_id"
              :title="title"
              class="smaller custom-mr-4"
            >
              <i class="fas fa-redo opacity-50" />
            </div>
          </div>
          <div class="flexbox full-width middle space-8 small">
            <div
              v-if="transaction.description || transaction.contractor_contact"
              class="wide width-50 black text-ellipsis"
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
              class="wide gray text-ellipsis"
            >
              {{ $t('noDesc') }}
            </span>

            <template v-if="account.name && !isCompactMode">
              <div
                class="wide text-ellipsis gray align-right"
                :title="account.name"
              >
                {{ account.name }}
              </div>
              <div
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
              </div>
            </template>
          </div>
        </div>

        <div
          v-else
          class="small align-center custom-my-8"
        >
          <div
            :style="`color: ${category.color}`"
            class="bold nowrap"
          >
            {{ `${transaction.amountShorten} ${$helper.currencySignByCode(account.currency)}` }}
          </div>
          <div class="black">
            {{ $moment(transaction.date).toDate().toLocaleDateString($moment.locale(), { month: 'short', day: 'numeric' }) }}
          </div>
          <div class="hint">
            {{ $moment(transaction.date).format('dddd') }}<br>
            {{ $moment(transaction.date).from($moment().startOf('day')) }}
          </div>
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
            <span
              v-if="transaction.contractor_contact?.name"
              class="small"
            >{{ transaction.contractor_contact.name }}</span>
            <span
              v-if="category"
              :style="`color: ${category.color}`"
              class="bold nowrap small text-ellipsis"
            >{{ category.name }}</span>
            <span class="hint text-ellipsis">{{ transaction.description || $t('noDesc') }}</span>
          </div>
        </div>
      </div>
    </template>
  </component>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import TransactionListCompleteButton from './TransactionListCompleteButton'
import TransactionListGroupRowDesc from './TransactionListGroupRowDesc'
import TransactionListGroupRowGlyph from './TransactionListGroupRowGlyph'
import {
  useFloating, flip,
  shift
} from '@floating-ui/vue'
import { appStateService } from '@/services/appState'

const reference = ref(null)
const floating = ref(null)
const openFloating = ref(false)

const { floatingStyles } = useFloating(reference, floating, {
  placement: 'bottom-start',
  middleware: [flip(), shift()]
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
    },

    tableLayout: {
      type: Boolean,
      default: false
    },

    showBalance: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      open: false,
      offBadgeInTransactionModal: false
    }
  },

  computed: {
    title () {
      return this.$tc(
        `repeatingInfo.interval.${this.transaction.repeating_data.interval}`,
        this.transaction.repeating_data.frequency,
        {
          frequency: this.transaction.repeating_data.frequency
        }
      )
    },

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

    scenario () {
      const id = this.transaction?.scenario_id
      if (!id) return null
      return this.$store.getters['scenario/getById'](id) || null
    },

    isCollapseHeader () {
      return !!this.collapseHeaderData
    },

    isChecked () {
      return this.$store.getters['transactionBulk/isSelected'](
        this.transaction.id
      )
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

    dateMain () {
      const date = this.$moment(this.transaction.date)
      return date.format(this.$moment.locale() === 'ru' ? 'D MMM' : 'MMM D')
    },

    dateWeekday () {
      const date = this.$moment(this.transaction.date)
      if (date.year() !== this.$moment().year()) return ''
      return date.format('dddd')
    },

    dateYear () {
      return this.$moment(this.transaction.date).format('YYYY')
    },

    dateLabel () {
      return `${this.dateMain} ${this.dateWeekday || this.dateYear}`
    },

    categoryLabel () {
      if (!this.category || !this.category.name) return ''
      const parentId = this.category.parent_category_id
      if (!parentId) return this.category.name
      const parent = this.$store.getters['category/getById'](parentId)
      return parent && parent.name ? `${parent.name} / ${this.category.name}` : this.category.name
    },

    contractorName () {
      return (this.transaction.contractor_contact && this.transaction.contractor_contact.name) || ''
    },

    descriptionText () {
      return this.transaction.description || ''
    },

    copyAmount () {
      const value = this.isCollapseHeader ? this.collapseHeaderData.totalAmount : this.transaction.amount
      return this.formatCopyNumber(value)
    },

    copyBalance () {
      if (!this.showBalance) return ''
      return this.formatCopyNumber(this.transaction.balance)
    },

    repeatingTitle () {
      if (this.isRepeatingGroup) {
        return this.$t('repeatingTransactionDesc', {
          repeats: this.transaction.affected_transactions
        })
      }
      if (this.transaction.repeating_id && this.transaction.repeating_data) {
        return this.title
      }
      return ''
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
        'c-transaction-table-row': this.tableLayout,
        'c-item--updated':
    this.transaction.$_flagUpdated || this.transaction.$_flagCreated
      }
    }
  },

  methods: {
    formatCopyNumber (value) {
      if (value === null || value === undefined || value === '') return ''
      const number = Number(value)
      if (!Number.isFinite(number)) return ''
      return number.toFixed(2)
    },

    handleClick (e) {
      if (this.tableLayout && window.getSelection && window.getSelection().toString()) {
        return
      }
      if (this.isCollapseHeader) {
        this.$emit('toggleCollapseHeader')
      } else if (this.isRepeatingGroup) {
        e.preventDefault()
      } else if (appStateService.webView && this.$helper.showMultiSelect()) {
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

<style scoped>
.dropdown-body {
  position: relative;
  display: block;
  left: auto;
  top: auto;
}
</style>

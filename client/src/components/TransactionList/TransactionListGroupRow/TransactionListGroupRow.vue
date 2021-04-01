<template>
  <li
    class="item c-item"
    ref="row"
    :class="classes"
    :style="isRepeatingGroup && 'cursor: initial;'"
  >
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
      <!-- TODO: create computed property for v-show -->
        <span
          v-show="isHoverComputed && !isCollapseHeader && !isRepeatingGroup"
          @click="checkboxSelect"
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
      <div>
        <span
          v-if="transaction.contractor_contact"
          class="icon userpic size-48"
        >
          <img :src="transaction.contractor_contact.userpic" alt="" />
        </span>
        <span
          v-else
          class="userpic userpic48 align-center"
          :style="`background-color:${category.color};`"
          ><i class="c-category-glyph fas" :class="glyph"></i
        ></span>
        <span
          v-show="isCollapseHeader || isRepeatingGroup"
        >
          <span
            class="userpic-stack-imitation"
            :style="`background-color:${category.color};`"
            ></span>
          <span
            class="userpic-stack-imitation"
            :style="`background-color:${category.color};`"
            ></span>
        </span>
      </div>
      <div
        class="wide flexbox middle space-4 c-item-border"
        style="overflow: hidden"
      >
        <div class="wide" style="overflow: hidden">
          <TransactionListGroupRowDesc
            :transaction="transaction"
            :collapseHeaderData="collapseHeaderData"
            :isRepeatingGroup="isRepeatingGroup"
            :category="category"
          />
          <TransactionListGroupRowCats
            :collapseHeaderData="collapseHeaderData"
            :category="category"
            :account="account"
          />
        </div>
        <div class="c-item-amount">
          <div class="custom-mb-8 align-right">
            <div :style="`color: ${category.color}`" class="bold nowrap">
              {{
                $helper.toCurrency({
                  value: isCollapseHeader
                    ? collapseHeaderData.totalAmount
                    : transaction.amount,
                  currencyCode: account.currency,
                  isDynamics: true,
                })
              }}
            </div>
          </div>
          <div class="small align-right nowrap">
            {{ $moment(transaction.date).format("ll") }}
          </div>
        </div>
        <transition name="fade" :duration="300">
          <TransactionListCompleteButton
            v-show="transaction.is_onbadge && $route.name === 'Upnext'"
            :transactionId="transaction.id"
            class="c-item-done"
          />
        </transition>
      </div>
    </div>

    <portal>
      <Modal v-if="open" @close="open = false">
        <AddTransaction :transaction="transaction" />
      </Modal>
    </portal>
  </li>
</template>

<script>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/Modals/AddTransaction'
import TransactionListCompleteButton from './TransactionListCompleteButton'
import TransactionListGroupRowDesc from './TransactionListGroupRowDesc'
import TransactionListGroupRowCats from './TransactionListGroupRowCats'
import currencyIcons from '@/utils/currencyIcons'
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
    }
  },

  data () {
    return {
      open: false,
      isHover: false
    }
  },

  components: {
    Modal,
    AddTransaction,
    TransactionListCompleteButton,
    TransactionListGroupRowDesc,
    TransactionListGroupRowCats
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
      if (process.env.VUE_APP_MODE === 'mobile') return true
      return this.showChecker ? true : this.isHover
    },

    glyph () {
      // if account currency has icon
      if (currencyIcons[this.account.currency]) {
        return currencyIcons[this.account.currency]
      }
      // if transfer
      if (this.transaction.category_id === -1312) {
        return 'fa-exchange-alt'
      }
      // if positive amount
      if (this.transaction.amount >= 0) {
        return 'fa-arrow-up'
      }
      // if negative amount
      if (this.transaction.amount < 0) {
        return 'fa-arrow-down'
      }
      return ''
    },

    classes () {
      return {
        'c-transaction-group': this.isCollapseHeader || this.isRepeatingGroup, // styles for the collapsed transactions
        'c-upcoming': this.$moment(this.transaction.date) > this.$moment() // styles for the upcoming transactions
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
        this.openModal(e)
      }
    },

    openModal ({ target }) {
      const path = []
      let currentElem = target
      while (currentElem) {
        path.push(currentElem)
        currentElem = currentElem.parentElement
      }

      if (
        path.some(
          e => e.className === 'wa-checkbox' || e.className === 'c-item-done'
        )
      ) {
        return
      }
      if (process.env.VUE_APP_MODE === 'mobile') {
        // emitting for the mobile platform
        window.emitter.emit('editTransaction', this.transaction)
      } else {
        this.open = true
      }
    },

    checkboxSelect () {
      const method = this.isChecked ? 'unselect' : 'select'
      this.$store.commit(`transactionBulk/${method}`, [this.transaction.id])
    }
  }
}
</script>

<style>
@keyframes updated {
  from {
    background-color: #dbf4e1;
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

.c-item-done {
  margin-left: 0.75rem;
  margin-top: 0.375rem;
}
</style>

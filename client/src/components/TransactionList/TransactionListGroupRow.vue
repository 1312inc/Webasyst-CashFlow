<template>
  <li class="c-item item" :class="classes">
    <div
      @mouseover="isHover = true"
      @mouseleave="isHover = false"
      @click="openModal"
      class="flexbox middle space-12"
    >
      <div v-if="$helper.showMultiSelect()" :class="{'desktop-only': $helper.isDesktopEnv}" style="width: 1rem;">
        <span
          v-show="isHoverComputed"
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
          ><i class="c-category-glyph fas fa-ruble-sign"></i
        ></span>
      </div>
      <div class="wide flexbox middle space-4 c-item-border" style="overflow: hidden;">
        <div class="wide" style="overflow: hidden;">
            <div class="flexbox space-4 semibold custom-mb-8" style="overflow: hidden;">
              <div v-if="transaction.description" class="black text-ellipsis" style="flex-shrink: 1;">{{ transaction.description }}</div>
              <span v-if="!transaction.description" class="gray">{{ $t('noDesc') }}</span>
              <span
                v-if="transaction.repeating_id"
                :title="$t('repeatingTran')"
              >
                <i class="fas fa-redo-alt opacity-50"></i>
              </span>
            </div>
            <div class="flexbox space-4 vertical-mobile small gray">
              <div v-if="category.name" class="text-ellipsis">
                {{ category.name }}
              </div>
              <span class="desktop-and-tablet-only">
                 /
              </span>
              <div v-if="account.name" class="text-ellipsis">
                {{ account.name }}
              </div>
            </div>
        </div>
        <div class="c-item-amount">
          <div class="custom-mb-8 align-right">
            <div :style="`color: ${category.color}`" class="bold">
              {{
                $helper.toCurrency({
                  value: transaction.amount,
                  currencyCode: account.currency,
                  isDynamics: true,
                })
              }}
            </div>
          </div>
          <div class="small align-right">
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

export default {
  props: {
    transaction: {
      type: Object
    },

    showChecker: {
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
    TransactionListCompleteButton
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

    isChecked () {
      return this.$store.getters['transactionBulk/isSelected'](
        this.transaction.id
      )
    },

    isHoverComputed () {
      if (process.env.VUE_APP_MODE === 'mobile') return true
      return this.showChecker ? true : this.isHover
    },

    classes () {
      return {
        'c-upcoming': this.$moment(this.transaction.date) > this.$moment(), // styles for the upcoming transactions
        'c-item--updated': this.$store.state.transaction.updatedTransactions
          .map(t => t.id)
          .includes(this.transaction.id)
      }
    }
  },

  methods: {
    openModal ({ target }) {
      const path = []
      let currentElem = target
      while (currentElem) {
        path.push(currentElem)
        currentElem = currentElem.parentElement
      }

      if (path.some(e => e.className === 'wa-checkbox' || e.className === 'c-item-done')) return
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
  animation-duration: 6s;
  animation-timing-function: linear;
  animation-direction: alternate;
  background-color: #dbf4e1;
}
.c-item-done {
  margin-left: 0.75rem;
  margin-top: 0.375rem;
}
/* .c-item-amount {
  margin-right: 0.75rem;
} */
</style>

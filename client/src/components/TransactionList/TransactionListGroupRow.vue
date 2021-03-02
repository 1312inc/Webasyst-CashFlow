<template>
  <li class="c-item item" :class="classes">
    <div
      @mouseover="isHover = true"
      @mouseleave="isHover = false"
      @click="openModal"
      class="flexbox middle space-12"
    >
      <div v-if="$helper.showMultiSelect()" style="width: 1rem;">
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
      <div class="wide flexbox c-item-border">
        <div class="wide">
            <div class="black semibold">
              <span v-if="transaction.description">{{ transaction.description }}</span>
              <span v-if="!transaction.description" class="gray">{{ $t('noDesc') }}</span>
              <span
                v-if="transaction.repeating_id"
                class="tooltip custom-ml-4"
                :data-title="$t('repeatingTran')"
              >
                <i class="fas fa-redo-alt opacity-50"></i>
              </span>
            </div>
            <span v-if="category.name" class="custom-mb-4 small gray">
              {{ category.name }}
            </span>
            <span v-if="account.name" class="small gray">
              / {{ account.name }}
            </span>
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
      </div>
      <transition name="fade" :duration="300">
        <TransactionListCompleteButton
          v-show="transaction.is_onbadge && $route.name === 'Upnext'"
          :transactionId="transaction.id"
          class="c-item-done"
        />
      </transition>
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
</style>

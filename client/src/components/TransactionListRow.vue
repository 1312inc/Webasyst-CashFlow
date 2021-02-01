<template>
  <li class="c-item item">
    <div @mouseover="isHover = true" @mouseleave="isHover = false" @click="openModal" class="flexbox middle space-12 custom-py-8">
      <div v-if="$helper.showMultiSelect()" style="width: 1rem">
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
          class="userpic userpic48 align-center"
          :style="`background-color:${category.color};`"
        ><i class="c-category-glyph fas fa-ruble-sign"></i></span>
      </div>
      <div class="wide">
        <div v-if="transaction.description" class="custom-mb-8 black semibold">
          {{ transaction.description }}
          <span
            v-if="transaction.repeating_id"
            class="tooltip custom-mr-8"
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
      <div>
        <div class="custom-mb-8 align-right">
          <div :style="`color: ${category.color}`" class="bold">
            {{ $helper.toCurrency(transaction.amount, account.currency, true) }}
          </div>
        </div>
        <div class="small align-right">
          {{ $moment(transaction.date).format("ll") }}
        </div>
      </div>
    </div>

    <Modal v-if="open" @close="open = false">
      <AddTransaction :transaction="transaction" />
    </Modal>
  </li>
</template>

<script>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'

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
    AddTransaction
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

      if (path.some(e => e.className === 'wa-checkbox')) return
      if (process.env.VUE_APP_MODE === 'mobile') {
        window.callAndroidAsync('editTransaction', this.transaction)
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

<template>
  <tr>
    <td v-if="showCheckbox" class="min-width">
      <input type="checkbox" @click="checkboxSelect" :checked="isChecked" />
    </td>
    <td @click="openModal" class="nowrap" style="width: 15%">
      {{ $moment(transaction.date).format("LL") }}
    </td>
    <td
      @click="openModal"
      class="nowrap tw-text-right"
      style="width: 15%"
      :style="`color: ${category.color}`"
    >
      {{ $helper.toCurrency(transaction.amount, account.currency) }}
    </td>
    <td @click="openModal" style="width: 20%">
      <div class="flexbox middle">
        <span class="icon smaller custom-mr-8">
          <i class="rounded" :style="`background-color:${category.color};`"></i>
        </span>
        {{ category.name }}
        <span
          v-if="transaction.repeating_id"
          class="tooltip custom-ml-8"
          :data-title="$t('repeatingTran')"
        >
          <i class="fas fa-redo-alt tw-opacity-50"></i>
        </span>
      </div>
    </td>
    <td @click="openModal" style="width: 29%">{{ transaction.description }}</td>
    <td @click="openModal" style="width: 20%">
      {{ account.name }}
    </td>

    <Modal v-if="open" @close="open = false">
      <AddTransaction :transaction="transaction" />
    </Modal>
  </tr>
</template>

<script>
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'

export default {
  props: {
    transaction: {
      type: Object
    }
  },

  data () {
    return {
      open: false
    }
  },

  components: {
    Modal,
    AddTransaction
  },

  computed: {
    account () {
      return this.$store.getters['account/getById'](this.transaction.account_id) || {}
    },

    category () {
      return this.$store.getters['category/getById'](this.transaction.category_id)
    },

    isChecked () {
      return this.$store.getters['transactionBulk/isSelected'](this.transaction.id)
    },

    showCheckbox () {
      return window.eventBus ? window.eventBus.multiSelect : true
    }
  },

  methods: {
    openModal ({ target }) {
      if (process.env.VUE_APP_MODE === 'mobile') {
        window.callAndroidAsync('editTransaction', this.transaction)
      } else {
        if (target.type === 'checkbox') return
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

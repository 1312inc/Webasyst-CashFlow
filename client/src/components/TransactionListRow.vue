<template>
  <tr>
    <td class="min-width">
      <input type="checkbox" @click="checkboxSelect" :checked="isChecked" />
    </td>
    <td @click="openModal">
      {{ $moment(transaction.date).format("LL") }}
    </td>
    <td @click="openModal">
      {{ $numeral(transaction.amount).format() }}
      {{ $helper.currToSymbol(accountById(transaction.account_id).currency) }}
    </td>
    <td @click="openModal">
      {{ category.name }}
    </td>
    <td @click="openModal">{{ transaction.description }}</td>
    <td @click="openModal">
      {{ accountById(transaction.account_id).name }}
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
    },

    isChecked: {
      type: Boolean
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
    category () {
      return this.$store.getters['category/getById'](
        this.transaction.category_id
      )
    }
  },

  methods: {
    accountById (id) {
      return this.$store.getters['account/getById'](id)
    },

    openModal ({ target }) {
      if (process.env.VUE_APP_MODE === 'mobile') {
        window.callAndroidAsync('editTransaction', this.transaction)
      } else {
        if (target.type === 'checkbox') return
        this.open = true
      }
    },

    checkboxSelect () {
      this.$emit('checkboxUpdate')
    }
  }
}
</script>

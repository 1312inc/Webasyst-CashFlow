<template>
  <div>
    <div
      @click="openModal"
      class="flex w-full cursor-pointer hover:bg-gray-200 border-b border-solid border-gray-300"
    >
      <div class="w-1/5 py-3">
        <div class="inline-flex items-center">
          <input
            type="checkbox"
            class="mr-2"
            @click="checkboxSelect"
            :checked="isChecked"
          />
          {{ $moment(transaction.date).format("LL") }}
        </div>
      </div>
      <div class="w-1/5 py-3">
        {{ $numeral(transaction.amount).format() }} {{ $helper.currToSymbol(accountById(transaction.account_id).currency) }}
      </div>
      <div class="w-1/5 py-3">
        <div class="flex items-center">
          <div
            class="w-3 h-3 rounded-full mr-2"
            :style="`background-color:${category.color};`"
          ></div>
          <div>
            {{ category.name }}
          </div>
        </div>
      </div>
      <div class="w-1/5 py-3">{{ transaction.description }}</div>
      <div class="w-1/5 py-3">
        {{ accountById(transaction.account_id).name }}
      </div>
    </div>

    <Modal v-if="open" @close="open = false">
      <AddTransaction :transaction="transaction" />
    </Modal>
  </div>
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
      return this.$store.getters['category/getById'](this.transaction.category_id)
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

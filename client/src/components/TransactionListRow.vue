<template>
  <div>
    <div
      @click="open = true"
      class="flex w-full cursor-pointer hover:bg-gray-200 border-b border-solid border-gray-300"
    >
      <div class="w-1/5 px-4 py-3">
        {{ $moment(transaction.date).format("LL") }}
      </div>
      <div class="w-1/5 px-4 py-3">
        {{ $numeral(transaction.amount).format("0,0 $") }}
      </div>
      <div class="w-1/5 px-4 py-3">
        <div class="flex items-center">
          <div
              class="w-3 h-3 rounded-full mr-2"
              :style="`background-color:${
                getCategoryById(transaction.category_id).color
              };`"
            ></div>
          <div>
            {{ getCategoryById(transaction.category_id).name }}
          </div>
        </div>
      </div>
      <div class="w-1/5 px-4 py-3">{{ transaction.description }}</div>
      <div class="w-1/5 px-4 py-3">
        {{ getAccountById(transaction.account_id).name }}
      </div>
    </div>

    <Modal v-if="open" @close="open = false">
      <AddTransaction :transaction="transaction" />
    </Modal>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
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
    ...mapGetters('account', ['getAccountById']),
    ...mapGetters('category', ['getCategoryById'])
  }
}
</script>

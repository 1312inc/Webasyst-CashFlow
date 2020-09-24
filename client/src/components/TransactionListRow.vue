<template>
    <tr>
        <td class="border px-4 py-2">
          <div @click="open = true">{{ $moment(transaction.date).format("LL") }}</div>
          <Modal v-if="open" @close="open = false">
              <AddTransaction :transaction="transaction" />
          </Modal>
        </td>
        <td class="border px-4 py-2">
          {{ $numeral(transaction.amount).format("0,0 $") }}
        </td>
        <td class="border px-4 py-2">
          <div class="flex items-center">
            <div>
              <div
                class="w-3 h-3 rounded-full mr-2"
                :style="`background-color:${
                  getCategoryById(transaction.category_id).color
                };`"
              ></div>
            </div>
            <div>
              {{ getCategoryById(transaction.category_id).name }}
            </div>
          </div>
        </td>
        <td class="border px-4 py-2">{{ transaction.description }}</td>
        <td class="border px-4 py-2">{{ getAccountById(transaction.account_id).name }}</td>

      </tr>
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

<template>
  <div v-if="currentEntity">
    <div>{{ $t("cashToday") }}</div>
    <div class="flexbox middle space-1rem">
      <div class="h2 custom-mb-0">
        {{ currentEntity.name || currentEntity.currency }}
      </div>
      <div
        v-if="balance"
        :class="balance >= 0 ? 'tw-text-green-500' : 'tw-text-red-500'"
        class="h2 custom-mb-0"
      >
        {{ $helper.toCurrency(balance, currentEntity.currency) }}
      </div>
      <div v-if="currentEntity.id > 0">
        <button @click="update(currentEntity)" class="button nobutton smaller">
          <i class="fas fa-sliders-h"></i> {{ $t("changeSettings") }}
        </button>
      </div>
    </div>
    <Modal v-if="open" @close="close">
      <component :is="currentComponentInModal" :editedItem="item"></component>
    </Modal>
  </div>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'
export default {
  components: {
    Modal,
    Account,
    Category
  },

  data () {
    return {
      open: false,
      currentComponentInModal: '',
      item: null
    }
  },

  computed: {
    currentEntity () {
      if (this.$store.state.currentType === 'account' || this.$store.state.currentType === 'category') {
        return this.$store.getters.getCurrentType
      }
      return this.$store.getters['balanceFlow/getBalanceFlowByCode'](
        this.$store.state.currentTypeId
      )
    },

    balance () {
      return (
        this.currentEntity.stat?.balance ||
        this.currentEntity.balances?.now.amount
      )
    }
  },

  methods: {
    update (item) {
      this.open = true
      this.currentComponentInModal = item.currency ? 'Account' : 'Category'
      this.item = item
    },

    close () {
      this.open = false
      this.currentComponentInModal = ''
    }
  }
}
</script>

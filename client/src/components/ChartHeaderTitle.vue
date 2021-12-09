<template>
  <div v-if="currentEntity" class="custom-p-16-mobile custom-pb-0-mobile">
    <div>{{ this.$moment().format("LL") }}</div>
    <div class="flexbox middle space-12">
      <div class="h2 custom-mb-0">
        {{ currentEntity.name || currentEntity.currency }}
      </div>
      <div
        v-if="balance"
        :class="balance >= 0 ? 'text-green' : 'text-red'"
        class="h2 nowrap custom-mb-0"
      >
        {{
          $helper.toCurrency({
            value: balance,
            currencyCode: currentEntity.currency
          })
        }}

      </div>

      <chart-header-title-average :currencyCode="currentEntity.currency" />

      <div v-if="currentEntity.id > 0 && $permissions.isAdmin">
        <button @click="update(currentEntity)" class="button nobutton">
          <i class="fas fa-edit"></i>
        </button>
      </div>
    </div>
    <portal>
      <Modal v-if="open" @close="close">
        <component :is="currentComponentInModal" :editedItem="item"></component>
      </Modal>
    </portal>
  </div>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/Modals/AddAccount'
import Category from '@/components/Modals/AddCategory'
import ChartHeaderTitleAverage from './ChartHeaderTitleAverage.vue'
export default {
  components: {
    Modal,
    Account,
    Category,
    ChartHeaderTitleAverage
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
      if (
        this.$store.state.currentType === 'account' ||
        this.$store.state.currentType === 'category'
      ) {
        return this.$store.getters.getCurrentType
      }
      return this.$store.getters['balanceFlow/getBalanceFlowByCode'](
        this.$store.state.currentTypeId
      )
    },

    balance () {
      return (
        this.currentEntity.stat?.summary ||
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

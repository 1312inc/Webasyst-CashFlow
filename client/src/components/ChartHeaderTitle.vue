<template>
  <div
    v-if="currentEntity"
    class="custom-pb-16-mobile"
  >
    <div>{{ $moment().format("LL") }}</div>
    <div class="flexbox middle space-12 wrap">
      <div
        class="flexbox space-12 middle wrap-mobile"
        style="min-width: 0;"
      >
        <div class="h2 custom-mb-0 text-ellipsis">
          {{ currentEntity.name || currentEntity.currency }}
        </div>
        <div
          v-if="currentEntity.currency"
          :class="{
            'text-green': balance > 0,
            'text-red': balance < 0
          }"
          class="h2 nowrap custom-mb-0"
        >
          {{
            $helper.toCurrency({
              value: balance,
              currencyCode: currentEntity.currency
            })
          }}
        </div>
      </div>

      <!-- TODO: make current currency as getter -->
      <chart-header-title-average :currency-code="$store.getters['transaction/activeCurrencyCode']" />

      <div v-if="currentEntity.id > 0 && $permissions.isAdmin">
        <button
          class="button nobutton circle"
          @click="update(currentEntity)"
        >
          <i class="fas fa-edit" />
        </button>
      </div>
    </div>
    <portal>
      <Modal
        v-if="open"
        @close="close"
      >
        <component
          :is="currentComponentInModal"
          :edited-item="item"
        />
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

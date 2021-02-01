<template>
  <div v-if="$helper.isDesktopEnv">
    <div
      v-sticky=!notStick
      sticky-offset="{top: 64}"
      sticky-z-index="12"
      class="c-sticky-header-controls"
    >
      <div class="custom-py-12 flexbox middle space-12">
        <div v-if="checkedRows.length" class="flexbox space-12 middle wide" :style="direction === 'column' && 'flex-direction: column;'">
          <button @click="openMove = true" class="yellow red" :class="direction === 'column' && 'custom-mb-12'">
            <i class="fas fa-arrow-right"></i> {{ $t("move") }}
            {{ checkedRows.length }}
          </button>
          <button @click="bulkDelete" class="button red" :class="direction === 'column' && 'custom-mb-12'">
            <i class="fas fa-trash-alt"></i> {{ $t("delete") }}
            {{ checkedRows.length }}
          </button>
          <button @click="unselectAll" class="button nobutton smaller">
            {{ $t("unselectAll") }}
          </button>
        </div>

        <div
          v-if="!checkedRows.length && currentType"
          class="flexbox space-12 middle wide"
        >
          <div v-if="currentType.type !== 'expense'">
            <button @click="addTransaction('income')" class="button green">
              <i class="fas fa-plus"></i> {{ $t("addIncome") }}
            </button>
          </div>
          <div v-if="currentType.type !== 'income'">
            <button @click="addTransaction('expense')" class="button orange">
              <i class="fas fa-minus"></i> {{ $t("addExpense") }}
            </button>
          </div>
          <div
            v-if="
              currentType.type !== 'expense' &&
              currentType.type !== 'income' &&
              $permissions.canAccessTransfers
            "
          >
            <button @click="addTransaction('transfer')" class="nobutton">
              <i class="fas fa-arrow-right"></i> {{ $t("transfer") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <portal>
      <Modal v-if="open" @close="open = false">
        <AddTransaction :defaultCategoryType="categoryType" />
      </Modal>
    </portal>

    <portal>
      <Modal v-if="openMove" @close="openMove = false">
        <TransactionMove />
      </Modal>
    </portal>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import AddTransaction from '@/components/AddTransaction'
import TransactionMove from '@/components/TransactionMove'
import utils from '@/mixins/utilsMixin.js'
export default {
  mixins: [utils],

  props: ['direction', 'notStick'],

  components: {
    Modal,
    AddTransaction,
    TransactionMove
  },

  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false
    }
  },

  computed: {
    ...mapGetters({
      currentType: 'getCurrentType'
    }),

    checkedRows () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
    }
  },

  beforeDestroy () {
    this.$store.commit('transactionBulk/empty')
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    },

    bulkDelete () {
      if (confirm(this.$t('bulkDeleteWarning'))) {
        this.$store.dispatch('transactionBulk/bulkDelete').catch(e => {
          this.handleApiError(e)
        })
      }
    },

    unselectAll () {
      this.$store.commit('transactionBulk/empty')
    }
  }
}
</script>

<style>
.c-sticky-header-controls {
  background-color: var(--background-color-blank);
}
</style>

<template>
  <div sticky-container class="custom-mb-24">
    <div @mouseover="isHover = true" @mouseleave="isHover = false">
      <div
        v-sticky
        sticky-offset="{top: 114, bottom: 10}"
        sticky-side="both"
        sticky-z-index="11"
        on-stick="onStick"
        class="c-sticky-header-group"
      >
        <div class="flexbox flexbox-mobile middle custom-py-8">
          <div class="flexbox middle space-12 wide">
            <div v-if="$helper.showMultiSelect()" style="width: 1rem">
              <div v-if="group.length">
                <span
                  v-show="isHoverComputed"
                  @click="checkAll(group)"
                  class="wa-checkbox"
                >
                  <input
                    type="checkbox"
                    :checked="isCheckedAllInGroup(group)"
                  />
                  <span>
                    <span class="icon">
                      <i class="fas fa-check"></i>
                    </span>
                  </span>
                </span>
              </div>
            </div>
            <h3 v-if="title" class="c-transaction-section-header">
              <div v-if="title === 'today'" class="black">
                {{ $t("today") }}
              </div>
              <div v-else class="black">
                {{ $moment(title).format("MMMM YYYY") }}
              </div>
            </h3>
          </div>
          <div class="flexbox middle space-12">
            <div class="hint">
              {{ $t("transactionsListCount", { count: group.length }) }}
            </div>
            <div>
              <AmountForGroup :group="group" type="income" />
            </div>
            <div>
              <AmountForGroup :group="group" type="expense" />
            </div>
          </div>
        </div>
      </div>

      <ul v-if="group.length" class="c-list list">
        <TransactionListRow
          v-for="transaction in group"
          :key="transaction.id"
          :transaction="transaction"
          :showChecker="isShowChecker"
        />
      </ul>

      <div v-else class="align-center custom-py-20">
        {{ $t("emptyList") }}
      </div>
    </div>
  </div>
</template>

<script>
import TransactionListRow from '@/components/TransactionListRow'
import AmountForGroup from '@/components/AmountForGroup'
export default {
  props: {
    group: {
      type: Array,
      required: true
    },

    title: {
      type: String
    }
  },

  components: {
    TransactionListRow,
    AmountForGroup
  },

  data () {
    return {
      isHover: false
    }
  },

  computed: {
    isShowChecker () {
      return this.group.some(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    isHoverComputed () {
      if (process.env.VUE_APP_MODE === 'mobile') return true
      return this.isShowChecker ? true : this.isHover
    }
  },

  methods: {
    checkAll (items) {
      const ids = items.map(e => e.id)
      const method = this.isCheckedAllInGroup(items) ? 'unselect' : 'select'
      this.$store.commit(`transactionBulk/${method}`, ids)
    },

    isCheckedAllInGroup (items) {
      return items.every(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    onStick (e) {
      if (e.top && e.sticked) {
        this.$store.commit('transaction/setActiveGroupTransactions', this.group)
      }
    }
  }
}
</script>

<style>
@media screen and (max-width: 760px) {
  .flexbox-mobile {
    flex-direction: column;
  }
  .flexbox-mobile.middle,
  .flexbox-mobile > *.middle {
    align-self: baseline;
  }
}

.c-sticky-header-group {
  background: #fff;
}

.c-sticky-header-group.vue-sticky-el.top-sticky {
  box-shadow: 0 5px 10px -5px rgba(0, 0, 0, 0.1);
}
</style>

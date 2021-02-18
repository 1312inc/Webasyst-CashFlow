<template>
  <div sticky-container class="custom-mt-24 c-transaction-section">
    <div
      @mouseover="
        isHover = true;
        $refs.pieIcon.style.display = 'block';
      "
      @mouseleave="
        isHover = false;
        $refs.pieIcon.style.display = 'none';
      "
    >
      <div
        v-sticky
        :sticky-offset="stickyOffset"
        sticky-z-index="11"
        on-stick="onStick"
        class="c-sticky-header-group"
      >
        <div class="flexbox middle custom-py-8">
          <div class="flexbox middle space-12 wide">
            <div v-if="$helper.showMultiSelect()" style="width: 1rem">
              <span
                v-show="isHoverComputed"
                @click="checkAll(filteredTransactions)"
                class="wa-checkbox"
              >
                <input
                  type="checkbox"
                  :checked="isCheckedAllInGroup(filteredTransactions)"
                />
                <span>
                  <span class="icon">
                    <i class="fas fa-check"></i>
                  </span>
                </span>
              </span>
            </div>

            <h3 class="c-transaction-section__header">
              <div v-if="type === 'overdue'" class="black">
                {{ $t("overdue") }}
              </div>
              <div v-if="type === 'yesterday'" class="black">
                {{ $t("yesterday") }}
              </div>
              <div v-if="type === 'today'" class="black">
                {{ $t("today") }}
              </div>
              <div
                v-if="type === 'future'"
                @click="toggleupcomingBlockOpened"
                :class="{ 'opacity-50': !upcomingBlockOpened }"
                class="black flexbox middle space-8"
                style="cursor: pointer"
              >
                <i class="fas fa-caret-down"></i>
                <span v-if="featurePeriod === 1">{{ $t("tomorrow") }}</span>
                <span v-else>{{
                  $t("nextDays", { count: featurePeriod })
                }}</span>
                <span>({{ filteredTransactions.length }})</span>
              </div>
              <div v-if="$moment(type).isValid()" class="black">
                {{ $moment(type).format("MMMM YYYY") }}
              </div>
            </h3>
            <TransactionListGroupUpcomingPeriod v-if="type === 'future'" />
            <div @click="onStick({sticked: true})" class="desktop-only" style="display: none;cursor: pointer;" ref="pieIcon">
              <i class="fas fa-chart-pie"></i>
            </div>
          </div>
          <div class="flexbox middle space-12">
            <AmountForGroup :group="filteredTransactions" type="income" />
            <AmountForGroup :group="filteredTransactions" type="expense" />
          </div>
        </div>
      </div>

      <div v-if="upcomingBlockOpened">
        <transition-group name="list" tag="ul" class="c-list list">
          <TransactionListGroupRow
            v-for="transaction in type === 'future'
              ? [...filteredTransactions].reverse()
              : filteredTransactions"
            :key="transaction.id"
            :transaction="transaction"
            :showChecker="isShowChecker"
          />
        </transition-group>
      </div>
    </div>
  </div>
</template>

<script>
import TransactionListGroupUpcomingPeriod from '@/components/TransactionList/TransactionListGroupUpcomingPeriod'
import TransactionListGroupRow from '@/components/TransactionList/TransactionListGroupRow'
import AmountForGroup from '@/components/AmountForGroup'
export default {
  props: {
    group: {
      type: Array,
      required: true
    },
    type: {
      type: String
    },
    index: {
      type: Number
    }
  },

  components: {
    TransactionListGroupUpcomingPeriod,
    TransactionListGroupRow,
    AmountForGroup
  },

  data () {
    return {
      isHover: false
    }
  },

  computed: {
    isShowChecker () {
      return this.filteredTransactions.some(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    isHoverComputed () {
      if (process.env.VUE_APP_MODE === 'mobile') return true
      return this.isShowChecker ? true : this.isHover
    },

    stickyOffset () {
      return this.$helper.isDesktopEnv ? '{"top": 114}' : '{"top": 0}'
    },

    featurePeriod () {
      return this.$store.state.transaction.featurePeriod
    },

    upcomingBlockOpened: {
      get () {
        if (this.type !== 'future') return true
        return this.$store.state.transaction.upcomingBlockOpened
      },

      set (val) {
        this.$store.commit('transaction/setUpcomingBlockOpened', val)
      }
    },

    filteredTransactions () {
      let result = this.group
      if (this.type === 'future') {
        const today = this.$moment()
          .add(1, 'd')
          .format('YYYY-MM-DD')
        result = this.group.filter(t => {
          const istart = this.$moment(t.date)
          return istart.diff(today, 'days') <= this.featurePeriod
        })
      }
      return result
    }
  },

  watch: {
    filteredTransactions () {
      if (this.$store.state.transaction.activeGroupTransactions.index === this.index) {
        this.onStick({ sticked: true })
      }
    }
  },

  created () {
    if (this.index === 0) {
      this.onStick({ sticked: true })
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
      if (e.sticked) {
        this.$store.commit('transaction/setActiveGroupTransactions', {
          index: this.index,
          name: this.type,
          items: this.filteredTransactions
        })
      }
    },

    toggleupcomingBlockOpened () {
      this.upcomingBlockOpened = this.upcomingBlockOpened ? 0 : 1
      localStorage.setItem(
        'upcoming_transactions_show',
        this.upcomingBlockOpened ? 1 : 0
      )
    }
  }
}
</script>

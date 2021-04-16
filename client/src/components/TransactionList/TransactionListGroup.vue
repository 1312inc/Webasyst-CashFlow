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
        <div class="flexbox middle wrap-mobile justify-between custom-py-8">
          <div class="flexbox middle space-12">
            <div
              v-if="$helper.showMultiSelect()"
              :class="{ 'desktop-only': $helper.isDesktopEnv }"
              style="min-width: 1rem"
            >
              <span
                v-show="isHoverComputed && filteredTransactions.length"
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

            <h3 class="c-transaction-section__header nowrap">
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
                <span v-if="featurePeriod === 1">{{ $t("tomorrow") }}</span>
                <span v-else>{{
                  $t("nextDays", { count: featurePeriod })
                }}</span>
              </div>
              <div
                v-if="$moment(new Date(type)).isValid()"
                class="black"
                style="text-transform: capitalize"
              >
                {{ $moment(type).format("MMMM YYYY") }}
              </div>
            </h3>
            <!-- (do we really need this for the upcoming block only?)
            <span v-if="type === 'future'" class="badge light-gray">{{
              filteredTransactions.length
            }}</span>
            -->
            <TransactionListGroupUpcomingPeriod v-if="type === 'future'" />
          </div>
          <div class="flexbox middle space-12">
            <div
              @click="onStick({ sticked: true })"
              class="desktop-only c-pie-icon-helper"
              style="display: none; cursor: pointer"
              ref="pieIcon"
            >
              <i class="fas fa-chart-pie"></i>
            </div>
            <Amounts
              :group="filteredTransactions"
              target="Group"
              class="flexbox justify-end middle wrap space-12"
            />
          </div>
        </div>
      </div>

      <div v-if="upcomingBlockOpened">
        <ul v-if="filteredTransactions.length" class="c-list list">
          <TransactionListGroupRow
            v-show="isShown(transaction)"
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            :transaction="transaction"
            :showChecker="isShowChecker"
            :collapseHeaderData="collapseHeaderData(transaction)"
            @toggleCollapseHeader="handleCollapseHeaderClick(transaction)"
          />
        </ul>
        <div v-else class="align-center custom-py-24">
          {{ $t("emptyList") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TransactionListGroupUpcomingPeriod from './TransactionListGroupUpcomingPeriod'
import TransactionListGroupRow from './TransactionListGroupRow/TransactionListGroupRow'
import Amounts from '@/components/Amounts'
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
    Amounts
  },

  data () {
    return {
      isHover: false,
      сollapseGroups: {},
      activeCollapseExternalSourceIDs: []
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
          return istart.diff(today, 'days') < this.featurePeriod
        })
      }

      return result
    }
  },

  watch: {
    filteredTransactions: {
      handler (val) {
        if (
          this.$store.state.transaction.activeGroupTransactions.index ===
          this.index
        ) {
          this.onStick({ sticked: true })
        }

        const hash = {}
        val
          .filter(e => e.external_source)
          .forEach(e => {
            const key = `${e.category_id}|${e.date}|${e.external_source}`
            if (!hash[key]) {
              hash[key] = {
                ids: [e.id],
                totalAmount: e.amount
              }
            } else {
              hash[key].ids.push(e.id)
              hash[key].totalAmount += e.amount
            }
          })

        // remove keys with only one transaction
        for (const k in hash) {
          if (hash[k].ids.length < 2) {
            delete hash[k]
          }
        }

        this.сollapseGroups = hash
      },
      immediate: true
    }
  },

  created () {
    if (this.index === 0) {
      this.onStick({ sticked: true })
    }
  },

  methods: {
    isShown (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      if (
        !this.сollapseGroups[key] ||
        this.сollapseGroups[key].ids[0] === transaction.id
      ) {
        return true
      }
      return this.activeCollapseExternalSourceIDs.includes(key)
    },

    collapseHeaderData (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      if (
        this.isShown(transaction) &&
        !this.activeCollapseExternalSourceIDs.includes(key)
      ) {
        return this.сollapseGroups[key]
      } else {
        return null
      }
    },

    handleCollapseHeaderClick (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      const i = this.activeCollapseExternalSourceIDs.indexOf(key)
      if (i > -1) {
        this.activeCollapseExternalSourceIDs.splice(i, 1)
      } else {
        this.activeCollapseExternalSourceIDs.push(key)
      }
    },

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

<style>
.c-pie-icon-helper {
  opacity: 0.5;
  transition: 0.2s opacity;
}
.c-pie-icon-helper:hover {
  opacity: 1;
}
</style>

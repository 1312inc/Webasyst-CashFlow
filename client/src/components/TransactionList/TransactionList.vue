<template>
  <div>
    <BlankBox v-if="$store.state.transaction.loading">
      <SkeletonTransaction />
    </BlankBox>
    <div v-else>
      <BlankBox>
        <TransactionListCreated />
      </BlankBox>

      <BlankBox
        v-for="(group, index) in groups.filter(g => ['overdue', 'future', 'tomorrow', 'today', 'yesterday'].includes(g.name))"
        :key="group.name"
      >
        <TransactionListGroup
          :group="group.items"
          :type="group.name"
          :index="index"
          :visible-select-checkbox="visibleSelectCheckbox"
          :show-founded-count="showFoundedCount"
        />
      </BlankBox>

      <div
        v-if="nextMonthIntervalLabel({name: $moment().format('YYYY-MM')}, onlyStreamGroups[0])"
        class="align-center small gray custom-pb-24"
      >
        {{ nextMonthIntervalLabel({name: $moment().format('YYYY-MM')}, onlyStreamGroups[0]) }}
      </div>

      <div
        v-for="(group, index) in onlyStreamGroups"
        :key="group.name"
      >
        <div
          v-if="nextMonthIntervalLabel(onlyStreamGroups[index - 1], group)"
          class="align-center small gray custom-pb-24"
        >
          {{ nextMonthIntervalLabel(onlyStreamGroups[index - 1], group) }}
        </div>

        <BlankBox>
          <TransactionListGroup
            :group="group.items"
            :type="group.name"
            :index="index"
            :visible-select-checkbox="visibleSelectCheckbox"
            :show-founded-count="showFoundedCount"
          />
        </BlankBox>
      </div>

      <Observer
        v-if="observer &&
          (isSplitFetchMode
            ? (pastTransactionsOffset && pastTransactionsOffset < transactions.total)
            : (transactions.data.length && transactions.data.length < transactions.total)
          )"
        @callback="() => { observerCallback(isSplitFetchMode ? pastTransactionsOffset : transactions.data.length) }"
      />

      <div v-if="(transactions.data.length === transactions.total) && transactions.data.length">
        <div class="align-center small gray custom-p-24">
          {{ $t('allTransactionsProcessed') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import TransactionListCreated from './TransactionListCreated'
import TransactionListGroup from './TransactionListGroup'
import SkeletonTransaction from './SkeletonTransaction'
import Observer from './Observer'
import BlankBox from '../BlankBox.vue'

export default {

  components: {
    TransactionListCreated,
    TransactionListGroup,
    SkeletonTransaction,
    Observer,
    BlankBox
  },
  props: {
    grouping: {
      type: Boolean,
      default: true
    },
    observer: {
      type: Boolean,
      default: true
    },
    upnext: {
      type: Boolean,
      default: false
    },
    showOverdueGroup: {
      type: Boolean,
      default: false
    },
    showFutureGroup: {
      type: Boolean,
      default: true
    },
    showTodayGroup: {
      type: Boolean,
      default: true
    },
    showYesterdayGroup: {
      type: Boolean,
      default: false
    },
    showTomorrowGroup: {
      type: Boolean,
      default: false
    },
    visibleSelectCheckbox: {
      type: Boolean,
      default: false
    },
    showFoundedCount: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    ...mapState('transaction', ['transactions', 'isSplitFetchMode']),
    ...mapGetters('transaction', ['isDetailsMode']),
    transactionsWithoutJustCreated () {
      return this.$store.getters['transaction/getTransactionsWithoutJustCreated']
    },
    pastTransactionsOffset () {
      return this.transactions.data.length - this.$store.getters['transaction/getFutureTransactions'].length
    },
    activeCurrencyCode () {
      return this.$store.getters['transaction/activeCurrencyCode']
    },
    transactionsByCurrency () {
      if (this.activeCurrencyCode) {
        return this.transactionsWithoutJustCreated.filter(t => {
          return this.$store.getters['account/accountsByCurrencyCode'](
            this.activeCurrencyCode
          ).includes(t.account_id)
        })
      } else {
        return this.transactionsWithoutJustCreated
      }
    },
    groups () {
      const today = this.$helper.currentDate
      const yesterday = this.$moment()
        .add(-1, 'day')
        .format('YYYY-MM-DD')
      const tomorrow = this.$moment()
        .add(1, 'day')
        .format('YYYY-MM-DD')
      const result = []

      const add = (name, transaction, reverse = false) => {
        const t = result.find(e => e.name === name)
        if (!t) {
          result.push({
            name,
            items: [transaction]
          })
        } else {
          if (reverse) {
            t.items.unshift(transaction)
          } else {
            t.items.push(transaction)
          }
        }
      }

      // add Future object
      if (this.showFutureGroupComputed) {
        result.push({
          name: 'future',
          items: []
        })
      }

      // add tomorrow object
      if (this.showTomorrowGroup && this.showRestGroupComputed) {
        result.push({
          name: 'tomorrow',
          items: []
        })
      }

      // add today object
      if (this.showTodayGroupComputed) {
        result.push({
          name: 'today',
          items: []
        })
      }

      // add yesterday object
      if (this.showYesterdayGroup && this.showRestGroupComputed) {
        result.push({
          name: 'yesterday',
          items: []
        })
      }

      if (this.upnext) {
        result.reverse()
      }

      // add Overdue object
      if (this.showOverdueGroup && this.transactionsByCurrency.some(e => e.date < today && e.is_onbadge)) {
        result.unshift({
          name: 'overdue',
          items: []
        })
      }

      this.transactionsByCurrency.forEach(e => {
        // if no grouping
        if (!this.grouping) {
          return add('ungroup', e)
        }

        // if overdue
        if (e.date < today && e.is_onbadge && this.showOverdueGroup) {
          return add('overdue', e)
        }

        // if tomorrow
        if (e.date === tomorrow && this.showTomorrowGroup && this.showRestGroupComputed) {
          return add('tomorrow', e)
        }

        // if future and not details mode
        if (e.date > today && this.showFutureGroupComputed) {
          return add('future', e, this.upnext)
        }

        // if today
        if (e.date === today && this.showTodayGroupComputed) {
          return add('today', e)
        }

        // if yesterday
        if (e.date === yesterday && this.showYesterdayGroup && this.showRestGroupComputed) {
          return add('yesterday', e)
        }

        // if past is not needed
        if (this.upnext) return

        // if past
        add(this.$moment(e.date).format('YYYY-MM'), e)
      })

      return result
    },

    onlyStreamGroups () {
      return this.groups.filter(g => !(['overdue', 'future', 'tomorrow', 'today', 'yesterday'].includes(g.name)))
    },

    showFutureGroupComputed () {
      return !this.isDetailsMode ? this.showFutureGroup : false
    },

    showTodayGroupComputed () {
      return !this.isDetailsMode ? this.showTodayGroup : false
    },

    showRestGroupComputed () {
      return !this.isDetailsMode
    }

  },

  methods: {
    observerCallback (offset) {
      this.$store.dispatch('transaction/fetchTransactions', {
        offset
      })
    },

    nextMonthIntervalLabel (groupFrom, groupTo) {
      const lastTransactionFromDate = groupFrom?.name || ''
      const firstTransactionToDate = groupTo?.name || ''

      if (!lastTransactionFromDate || !firstTransactionToDate) return ''

      const diffDays = this.$moment(lastTransactionFromDate + '-01').diff(this.$moment(firstTransactionToDate + '-01'), 'days')
      if (diffDays >= 3 * 365) {
        return this.$t('intervalLabels.eternity')
      } else if (diffDays >= 1.5 * 365) {
        return this.$t('intervalLabels.yearsPassed')
      } else if (diffDays >= 365) {
        return this.$t('intervalLabels.yearPassed')
      } else if (diffDays >= 6 * 30) {
        return this.$t('intervalLabels.monthsPassed')
      }

      return ''
    }
  }
}
</script>

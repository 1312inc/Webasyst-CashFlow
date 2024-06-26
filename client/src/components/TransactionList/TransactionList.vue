<template>
  <div>
    <div v-if="$store.state.transaction.loading">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <TransactionListCreated />
      <div
        v-for="(group, index) in groups"
        :key="group.name"
      >
        <TransactionListGroup
          :group="group.items"
          :type="group.name"
          :index="index"
          :visible-select-checkbox="visibleSelectCheckbox"
          :show-founded-count="showFoundedCount"
        />
      </div>
      <Observer
        v-if="observer &&
          (isSplitFetchMode
            ? (pastTransactionsOffset && pastTransactionsOffset < transactions.total)
            : (transactions.data.length && transactions.data.length < transactions.total)
          )"
        @callback="() => { observerCallback(isSplitFetchMode ? pastTransactionsOffset : transactions.data.length) }"
      />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import TransactionListCreated from './TransactionListCreated'
import TransactionListGroup from './TransactionListGroup'
import SkeletonTransaction from './SkeletonTransaction'
import Observer from './Observer'

export default {

  components: {
    TransactionListCreated,
    TransactionListGroup,
    SkeletonTransaction,
    Observer
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
    ...mapState('transaction', ['transactions', 'detailsInterval', 'isSplitFetchMode']),
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
      if (this.showTomorrowGroup) {
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
      if (this.showYesterdayGroup) {
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
        if (e.date === tomorrow && this.showTomorrowGroup) {
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
        if (e.date === yesterday && this.showYesterdayGroup) {
          return add('yesterday', e)
        }

        // if past is not needed
        if (this.upnext) return

        // if past
        add(this.$moment(e.date).format('YYYY-MM'), e)
      })

      return result
    },

    showFutureGroupComputed () {
      return !this.detailsInterval.from && !this.detailsInterval.to ? this.showFutureGroup : false
    },

    showTodayGroupComputed () {
      return !this.detailsInterval.from && !this.detailsInterval.to ? this.showTodayGroup : false
    }

  },

  methods: {
    observerCallback (offset) {
      this.$store.dispatch('transaction/fetchTransactions', {
        offset
      })
    }
  }
}
</script>

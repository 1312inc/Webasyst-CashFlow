<template>
  <div>
    <div v-if="$store.state.transaction.loading">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <TransactionListCreated />
      <div
        v-for="(group, index) in upnext ? [...groups].reverse() : groups"
        :key="group.name"
      >
        <TransactionListGroup
          :group="group.items"
          :type="group.name"
          :index="index"
        />
      </div>
      <Observer
        v-if="observer && transactions.data.length < transactions.total"
        @callback="observerCallback"
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
    }
  },

  components: {
    TransactionListCreated,
    TransactionListGroup,
    SkeletonTransaction,
    Observer
  },

  computed: {
    ...mapState('transaction', ['transactions', 'detailsInterval']),
    transactionsWithoutJustCreated () {
      return this.$store.getters['transaction/getTransactionsWithoutJustCreated']
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
      const add = (name, transaction, reverse = false) => {
        const t = result.find(e => e.name === name)
        if (!t) {
          result.push({
            name: name,
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
      const result = []

      // add Future object
      if (this.showFutureGroupComputed && !result.find(e => e.name === 'future')) {
        result.push({
          name: 'future',
          items: []
        })
      }

      // add today object
      if (this.showTodayGroupComputed && !result.find(e => e.name === 'today')) {
        result.push({
          name: 'today',
          items: []
        })
      }

      // add yesterday object
      if (
        this.showYesterdayGroup &&
        !result.find(e => e.name === 'yesterday')
      ) {
        result.push({
          name: 'yesterday',
          items: []
        })
      }

      this.transactionsByCurrency.forEach(e => {
        // if no grouping
        if (!this.grouping) {
          return add('ungroup', e)
        }

        // if future and not details mode
        if (
          e.date > today &&
          this.showFutureGroupComputed &&
          !this.$store.state.transaction.detailsInterval.from &&
          !this.$store.state.transaction.detailsInterval.to
        ) {
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

        // if past
        const month = this.upnext
          ? 'overdue'
          : this.$moment(e.date).format('YYYY-MM')
        add(month, e)
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
    observerCallback () {
      this.$store.dispatch('transaction/fetchTransactions', {
        offset: this.transactions.data.length
      })
    }
  }
}
</script>

<template>
  <div>
    <div v-if="$store.state.transaction.loading">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <transition name="fade">
        <TransactionListCreated
          v-if="$store.state.transaction.createdTransactions.length"
        />
      </transition>
      <div
        v-for="(group, index) in groups"
        :key="group.name"
      >
        <TransactionListGroup
          :group="group.items"
          :type="group.name"
          :index="index"
        />
      </div>
      <div v-if="!groups.length" class="align-center custom-py-24">
        {{ $t("emptyList") }}
      </div>
      <Observer
        v-if="observer && transactions.data.length < transactions.total"
        @callback="observerCallback"
      />
    </div>
  </div>
</template>

<script>
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
    reverse: {
      type: Boolean,
      default: false
    },
    observer: {
      type: Boolean,
      default: true
    },
    upnext: {
      type: Boolean,
      default: false
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
    transactions () {
      return this.$store.state.transaction.transactions
    },
    groups () {
      const today = this.$moment().format('YYYY-MM-DD')
      const yesterday = this.$moment().add(-1, 'day').format('YYYY-MM-DD')
      const add = (name, transaction) => {
        const t = result.find(e => e.name === name)
        if (!t) {
          result.push({
            name: name,
            items: [transaction]
          })
        } else {
          t.items.push(transaction)
        }
      }
      const result = []

      this.$store.state.transaction.transactions.data.forEach(e => {
        // if no grouping
        if (!this.grouping) {
          return add('ungroup', e)
        }

        // add today object if Upnext
        if (this.upnext && !result.find(e => e.name === 'today')) {
          result.push({
            name: 'today',
            items: []
          })
        }

        // if future and not details mode
        if (
          e.date > today &&
          !this.$store.state.transaction.detailsInterval.from &&
            !this.$store.state.transaction.detailsInterval.to
        ) {
          return add('future', e)
        }

        // add today object
        if (this.showTodayGroup && !result.find(e => e.name === 'today')) {
          result.push({
            name: 'today',
            items: []
          })
        }

        // if today
        if (e.date === today) {
          return add('today', e)
        }

        // add yesterday object
        if (this.showYesterdayGroup && !result.find(e => e.name === 'yesterday')) {
          result.push({
            name: 'yesterday',
            items: []
          })
        }

        // if yesterday
        if (e.date === yesterday) {
          return add('yesterday', e)
        }

        // if past
        const month = this.upnext
          ? 'overdue'
          : this.$moment(e.date).format('YYYY-MM')
        add(month, e)
      })

      return result
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

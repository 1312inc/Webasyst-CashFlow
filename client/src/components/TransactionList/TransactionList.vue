<template>
  <div>
    <div v-if="!this.$store.state.transaction.transactions.length">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <transition name="fade">
        <TransactionListCreated
          v-if="$store.state.transaction.createdTransactions.length"
        />
      </transition>
      <div v-for="(group, index) in (upnext ? [...groups].reverse() : groups)" :key="group.name">
        <TransactionListGroup :group="group.items" :type="group.name" :index="index" />
      </div>
      <Observer v-if="observer" @callback="$emit('offsetCallback')" />
    </div>
  </div>
</template>

<script>
import TransactionListCreated from '@/components/TransactionList/TransactionListCreated'
import TransactionListGroup from '@/components/TransactionList/TransactionListGroup'
import SkeletonTransaction from '@/components/SkeletonTransaction'
import Observer from '@/components/Observer'

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
    }
  },

  components: {
    TransactionListCreated,
    TransactionListGroup,
    SkeletonTransaction,
    Observer
  },

  computed: {
    groups () {
      const today = this.$moment().format('YYYY-MM-DD')
      const result = []

      function add (name, transaction) {
        const i = result.find(e => e.name === name)
        if (!i) {
          result.push({
            name: name,
            items: [transaction]
          })
        } else {
          i.items.push(transaction)
        }
      }

      this.$store.state.transaction.transactions.forEach(e => {
        // if no grouping
        if (!this.grouping) {
          return add('ungroup', e)
        }

        // if future
        if (e.date > today) {
          return add('future', e)
        }

        if (this.upnext) {
          // if yesterday
          const yesterday = this.$moment().add(-1, 'day').format('YYYY-MM-DD')
          if (e.date === yesterday) {
            return add('yesterday', e)
          }
        }

        // if today
        if (e.date === today) {
          return add('today', e)
        }

        // if past
        const month = this.upnext ? 'overdue' : this.$moment(e.date).format('YYYY-MM')
        add(month, e)
      })

      return result
    }
  }
}
</script>

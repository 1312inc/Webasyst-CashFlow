<template>
  <div>
    <div v-if="loading && !transactions.length">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <div class="align-right custom-mb-4">
        <ExportButton type="completed" />
      </div>
      <transition name="fade">
        <TransactionListCreated v-if="$store.state.transaction.createdTransactions.length" />
      </transition>
      <div v-for="(items, type, index) in grouppedTransactions" :key="type">
        <TransactionListGroup :group="items" :type="type" :index="index" />
      </div>
      <Observer v-if="showObserver" @callback="getTransactions" />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import api from '@/plugins/api'
import TransactionListCreated from '@/components/TransactionList/TransactionListCreated'
import TransactionListGroup from '@/components/TransactionList/TransactionListGroup'
import ExportButton from '@/components/ExportButton'
import SkeletonTransaction from '@/components/SkeletonTransaction'
import Observer from '@/components/Observer'

export default {
  props: {
    grouping: {
      type: Boolean,
      default: true
    },
    upcoming: {
      type: Boolean,
      default: false
    },
    reverse: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      grouppedTransactions: [],
      loading: false,
      result: {}
    }
  },

  components: {
    TransactionListCreated,
    TransactionListGroup,
    ExportButton,
    SkeletonTransaction,
    Observer
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    transactions: {
      get () {
        return this.$store.state.transaction.transactions
      },

      set (val) {
        this.$store.commit('transaction/setTransactions', val)
      }
    },

    showObserver () {
      return this.transactions.length < this.result.total
    }
  },

  watch: {
    transactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const result = this.transactions.reduce((acc, e) => {
        // if future
        if (e.date > today) {
          if (!('future' in acc)) acc.future = []
          this.reverse ? acc.future.unshift(e) : acc.future.push(e)
          return acc
        }

        // if today
        if (e.date === today) {
          if (!('today' in acc)) acc.today = []
          this.reverse ? acc.today.unshift(e) : acc.today.push(e)
          return acc
        }

        // if past
        const month = this.$moment(e.date).format('YYYY-MM')
        if (this.grouping) {
          if (!(month in acc)) acc[month] = []
          this.reverse ? acc[month].unshift(e) : acc[month].push(e)
        } else {
          if (!('ungroup' in acc)) acc.ungroup = []
          this.reverse ? acc.ungroup.unshift(e) : acc.ungroup.push(e)
        }
        return acc
      }, {})

      if (this.upcoming && result.future) {
        this.grouppedTransactions = {
          ungroup: result.future
        }
        return
      }

      this.grouppedTransactions = result
    }
  },

  created () {
    this.unsubscribeFromQueryParams = this.$store.subscribe((mutation) => {
      if ((mutation.type === 'transaction/updateQueryParams' || mutation.type === 'transaction/setDetailsInterval') && !mutation.payload.silent) {
        this.getTransactions({ offset: 0 })
      }
    })

    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action) => {
        if (
          (action.type === 'transactionBulk/bulkMove' ||
            action.type === 'category/delete') && !action.payload?.silent
        ) {
          this.getTransactions({ offset: 0 })
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromQueryParams()
    this.unsubscribeFromTransitionUpdate()
  },

  methods: {
    async getTransactions (customQueryParams = {}) {
      if (this.loading) return
      this.loading = true
      const defaultParams = { ...this.queryParams, from: '' }
      if (this.upcoming) {
        defaultParams.from = this.$moment()
          .add(1, 'd')
          .format('YYYY-MM-DD')
      }
      defaultParams.to = this.$moment()
        .add(1, 'M')
        .format('YYYY-MM-DD')

      if (this.detailsInterval.from) {
        defaultParams.from = this.detailsInterval.from
      }
      if (
        this.detailsInterval.to &&
        this.detailsInterval.to < defaultParams.to
      ) {
        defaultParams.to = this.detailsInterval.to
      }

      const params = {
        ...defaultParams,
        offset: this.transactions.length,
        ...customQueryParams
      }

      if (params.offset === 0) this.transactions = []

      try {
        const { data } = await api.get('cash.transaction.getList', {
          params
        })
        this.result = data
        this.transactions = [...this.transactions, ...this.result.data]
        this.loading = false
      } catch (_) {
        return false
      }
    }
  }
}
</script>

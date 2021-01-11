<template>
  <div>
    <div v-if="loading && !transactions.length">
      <!-- <SkeletonTransaction /> -->
    </div>
    <div v-else>
      <div class="align-right custom-mb-4">
        <ExportButton type="completed" />
      </div>
      <div
        v-for="(transactionGroup, group) in grouppedTransactions"
        :key="group"
      >
        <TransactionListGroup :group="transactionGroup" :title="group" />
      </div>
      <Observer v-if="showObserver" @callback="getTransactions" />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import api from '@/plugins/api'
import transactionListMixin from '@/mixins/transactionListMixin'
import TransactionListGroup from '@/components/TransactionListGroup'
import ExportButton from '@/components/ExportButton'
// import SkeletonTransaction from '@/components/SkeletonTransaction'
import Observer from '@/components/Observer'

export default {
  mixins: [transactionListMixin],

  props: {
    grouping: {
      type: Boolean,
      default () {
        return true
      }
    },

    upcoming: {
      type: Boolean,
      default () {
        return false
      }
    },

    reverse: {
      type: Boolean,
      default () {
        return false
      }
    }
  },

  data () {
    return {
      loading: false,
      result: {},
      transactions: []
    }
  },

  components: {
    TransactionListGroup,
    ExportButton,
    // SkeletonTransaction,
    Observer
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    showObserver () {
      return this.transactions.length < this.result.total
    },

    grouppedTransactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const acc = { today: [] }
      const result = this.transactions.reduce((acc, e) => {
        const month = this.$moment(e.date).format('YYYY-MM')
        if (e.date === today) {
          this.reverse ? acc.today.unshift(e) : acc.today.push(e)
          return acc
        }
        if (this.grouping) {
          if (month in acc) {
            this.reverse ? acc[month].unshift(e) : acc[month].push(e)
          } else {
            acc[month] = [e]
          }
        } else {
          if (!('items' in acc)) acc.items = []
          this.reverse ? acc.items.unshift(e) : acc.items.push(e)
        }
        return acc
      }, acc)
      return result
    }
  },

  methods: {
    async getTransactions (customQueryParams = {}) {
      if (this.loading) return
      this.loading = true
      const defaultParams = { ...this.queryParams }
      if (!this.upcoming) { defaultParams.to = this.$moment().format('YYYY-MM-DD') }
      if (this.detailsInterval.from) { defaultParams.from = this.detailsInterval.from }
      if (
        this.detailsInterval.to &&
        this.detailsInterval.to < defaultParams.to
      ) {
        defaultParams.to = this.detailsInterval.to
      }

      const params = { ...defaultParams, offset: this.transactions.length, ...customQueryParams }

      if (params.offset === 0) this.transactions = []

      const { data } = await api.get('cash.transaction.getList', {
        params
      })

      this.result = data
      this.transactions = [...this.transactions, ...this.result.data]

      this.loading = false
    }
  }
}
</script>

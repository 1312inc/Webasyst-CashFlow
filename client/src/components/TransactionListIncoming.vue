<template>
  <div>
    <div v-if="loading">
      <SkeletonTransaction />
    </div>
    <div v-else>
      <div class="flexbox middle space-12">
        <div class="wide"></div>
        <div>
          <NumPages
            :total="transactions.total"
            :limit="transactions.limit"
            :offset="transactions.offset"
            @changePage="changePage"
          />
        </div>
        <div>
          <ExportButton type="completed" />
        </div>
      </div>
      <div
        v-for="(transactionGroup, group) in grouppedTransactions"
        :key="group"
      >
        <TransactionListGroup :group="transactionGroup" :title="group" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import api from '@/plugins/api'
import transactionListMixin from '@/mixins/transactionListMixin'
import NumPages from '@/components/NumPages'
import TransactionListGroup from '@/components/TransactionListGroup'
import ExportButton from '@/components/ExportButton'
import SkeletonTransaction from '@/components/SkeletonTransaction'

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
      loading: true,
      transactions: []
    }
  },

  components: {
    NumPages,
    TransactionListGroup,
    ExportButton,
    SkeletonTransaction
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    filteredTransactions () {
      return this.transactions.data
    },

    isDetailsMode () {
      return this.detailsInterval.from !== '' || this.detailsInterval.to !== ''
    },

    grouppedTransactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const acc =
        this.transactions.offset === 0 && !this.isDetailsMode
          ? { today: [] }
          : {}
      const result = this.filteredTransactions.reduce((acc, e) => {
        const month = this.$moment(e.date).format('YYYY-MM')
        if (e.date === today && acc.today) {
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
      this.loading = true
      const defaultParams = { ...this.queryParams }
      if (!this.upcoming) defaultParams.to = this.$moment().format('YYYY-MM-DD')
      if (this.detailsInterval.from) {
        defaultParams.from = this.detailsInterval.from
      }
      if (
        this.detailsInterval.to &&
        this.detailsInterval.to < defaultParams.to
      ) {
        defaultParams.to = this.detailsInterval.to
      }

      const params = { ...defaultParams, ...customQueryParams }

      const { data } = await api.get('cash.transaction.getList', {
        params
      })

      this.transactions = data
      this.loading = false
    },

    changePage (offset) {
      this.getTransactions({ offset })
    }
  }
}
</script>

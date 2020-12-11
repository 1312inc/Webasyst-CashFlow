<template>
  <div>
    <div v-if="loading">
      <div class="skeleton">
        <table>
          <tr v-for="i in queryParams.limit" :key="i">
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
            <td>
              <span class="skeleton-line custom-mb-0"></span>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div v-else>
      <div class="flexbox middle space-1rem align-right">
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
        <div v-if="group === 'today'" class="black">
          {{ $t("today") }}
        </div>
        <div v-else class="black">
          {{ $moment(group).format("MMMM, YYYY") }}
        </div>
        <table v-if="transactionGroup.length" class="small zebra custom-mb-24">
          <tr>
            <th
              class="min-width tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
            >
              <input
                type="checkbox"
                @click="checkAll"
                v-model="checkboxChecked"
              />
            </th>
            <th
              colspan="5"
              class="tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
            >
              {{
                $t("transactionsListCount", { count: transactionGroup.length })
              }}
            </th>
          </tr>
          <TransactionListRow
            v-for="transaction in transactionGroup"
            :key="transaction.id"
            :transaction="transaction"
            :is-checked="checkedRows.includes(transaction.id)"
            @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
          />
        </table>
        <div v-else class="tw-text-center custom-py-20">
          {{ $t("emptyListToday") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import api from '@/plugins/api'
import transactionListMixin from '@/mixins/transactionListMixin'
import NumPages from '@/components/NumPages'
import TransactionListRow from '@/components/TransactionListRow'
import ExportButton from '@/components/ExportButton'
export default {
  mixins: [transactionListMixin],

  data () {
    return {
      loading: true,
      transactions: {},
      checkedRows: []
    }
  },

  components: {
    NumPages,
    TransactionListRow,
    ExportButton
  },

  watch: {
    queryParams: {
      handler () {
        this.getTransactions()
      },
      deep: true
    }
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
          acc.today.push(e)
          return acc
        }
        if (month in acc) {
          acc[month].push(e)
        } else {
          acc[month] = [e]
        }
        return acc
      }, acc)

      return result
    }
  },

  created () {
    this.unsubscribeFromTransitionUpdate = this.$store.subscribeAction({
      after: (action, state) => {
        if (
          action.type === 'transaction/update' ||
          action.type === 'transaction/delete'
        ) {
          this.getTransactions()
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribeFromTransitionUpdate()
  },

  methods: {
    async getTransactions (customQueryParams = {}) {
      this.loading = true
      const defaultParams = { ...this.queryParams }
      defaultParams.to = this.$moment().format('YYYY-MM-DD')
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

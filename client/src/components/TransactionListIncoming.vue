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
      <div class="flexbox middle space-12 align-right">
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

        <div class="flexbox middle">
          <div class="wide">
            <div v-if="group === 'today'" class="black">
              {{ $t("today") }}
            </div>
            <div v-else-if="group === 'items'" class="black">
              {{ $t("nextDays", {count: 7}) }}
            </div>
            <div v-else class="black">
              {{ $moment(group).format("MMMM, YYYY") }}
            </div>
          </div>
          <div class="flexbox middle space-12">
            <div>
              <AmountForGroup :group="transactionGroup" type="income" />
            </div>
            <div>
              <AmountForGroup :group="transactionGroup" type="expense" />
            </div>
          </div>
        </div>
        <table v-if="transactionGroup.length" class="small zebra custom-mb-24">
          <tr>
            <th
              v-if="showCheckbox"
              class="min-width tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
            >
              <input
                type="checkbox"
                @click="checkAll(transactionGroup)"
                :checked="isCheckedAllInGroup(transactionGroup)"
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
import AmountForGroup from '@/components/AmountForGroup'
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
    TransactionListRow,
    ExportButton,
    AmountForGroup
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

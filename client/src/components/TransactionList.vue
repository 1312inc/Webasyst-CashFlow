<template>
  <div>

    <div v-if="!loading" class="flexbox middle space-1rem custom-mb-24">
      <div v-if="checkedRows.length" class="flexbox space-1rem middle wide">
        <button @click="openMove = true" class="yellow red"><i class="fas fa-arrow-right"></i> {{ $t('move') }} {{ checkedRows.length }}</button>
        <button @click="bulkDelete" class="button red"><i class="fas fa-trash-alt"></i> {{ $t('delete') }} {{ checkedRows.length }}</button>
        <button @click="checkedRows = []" class="button nobutton smaller">{{ $t('unselectAll') }}</button>
      </div>

      <div
        v-if="!checkedRows.length && $helper.isDesktopEnv"
        class="flexbox space-1rem middle wide"
      >
        <div v-if="currentType.type !== 'expense'">
          <button @click="addTransaction('income')" class="button green">
            <i class="fas fa-plus"></i> {{ $t("addIncome") }}
          </button>
        </div>
        <div v-if="currentType.type !== 'income'">
          <button @click="addTransaction('expense')" class="button orange">
            <i class="fas fa-minus"></i> {{ $t("addExpense") }}
          </button>
        </div>
        <div v-if="currentType.type !== 'expense' && currentType.type !== 'income' && $permissions.canAccessTransfers">
          <button @click="addTransaction('transfer')" class="nobutton">
            <i class="fas fa-arrow-right"></i> {{ $t("transfer") }}
          </button>
        </div>
      </div>
      <div>
        <NumPages />
      </div>
      <div v-if="$helper.isDesktopEnv">
        <ExportButton />
      </div>
    </div>

    <div v-if="loading">
      <div class="skeleton">
        <span class="skeleton-custom-box" style="height: 36px"></span>
      </div>
    </div>

    <div v-if="loading">
      <div class="skeleton">
        <table>
          <tr v-for="i in transactions.data.length || transactions.limit" :key="i">
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

    <!-- <transition name="fade-appear"> -->
      <div v-if="!loading">
        <div v-if="isTransactionsExists">
          <div v-if="transactions.offset === 0 && !isDetailsMode && isShowFuture" class="custom-mb-24"> <!-- If firts page -->
            <div class="flexbox middle">
              <div @click="toggleupcomingBlockOpened" class="tw-cursor-pointer custom-mr-8 black" :class="{'tw-opacity-50': !upcomingBlockOpened}">
                <i class="fas fa-caret-down"></i>&nbsp;
                <span v-if="featurePeriod === 1">{{ $t('tomorrow') }}</span>
                <span v-else>{{ $t('nextDays', {'count': featurePeriod}) }}</span>&nbsp;
                <span v-if="upcomingTransactions.length">({{ upcomingTransactions.length }})</span>
              </div>
              <div @mouseover="$refs.dropdown.style.display = 'block'" @mouseleave="$refs.dropdown.style.display = 'none'" class="dropdown">
                <i class="fas fa-ellipsis-h"></i>
                <div class="dropdown-body" ref="dropdown">
                    <ul class="menu-v">
                        <li>
                            <a href="#" @click.prevent="setFeaturePeriod(1)"><span>{{ $t('tomorrow') }}</span></a>
                        </li>
                        <li>
                            <a href="#" @click.prevent="setFeaturePeriod(7)"><span>{{ $t('nextDays', {'count': 7}) }}</span></a>
                        </li>
                        <li>
                            <a href="#" @click.prevent="setFeaturePeriod(30)"><span>{{ $t('nextDays', {'count': 30}) }}</span></a>
                        </li>
                    </ul>
                </div>
              </div>
            </div>
            <div v-if="upcomingBlockOpened">
              <table
                class="small zebra custom-mt-12"
                v-if="upcomingTransactions.length"
              >
                <tr>
                  <th class="min-width tw-border-0 tw-border-b tw-border-solid tw-border-gray-400">
                    <!-- <input type="checkbox" @click="checkAll" /> -->
                  </th>
                  <th
                    colspan="5"
                    class="tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
                  >
                    {{
                      $t("transactionsListCount", { count: upcomingTransactions.length })
                    }}
                  </th>
                </tr>
                <TransactionListRow
                  v-for="transaction in upcomingTransactions"
                  :key="transaction.id"
                  :transaction="transaction"
                  :is-checked="checkedRows.includes(transaction.id)"
                  @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
                />
              </table>
              <div v-else class="tw-text-center custom-py-20">
                {{ $t('emptyListUpcoming') }}
              </div>
            </div>
          </div>

          <div v-for="(transitions, period) in incomingTransactions" :key="period">
            <div v-if="period === 'today'" class="black">
              {{ $t('today') }}
            </div>
            <div v-else class="black">
              {{ $moment(period).format('MMMM, YYYY') }}
            </div>
            <table
              class="small zebra custom-mb-24"
              v-if="transitions.length"
            >
              <tr>
                <th class="min-width tw-border-0 tw-border-b tw-border-solid tw-border-gray-400">
                  <!-- <input type="checkbox" @click="checkAll" /> -->
                </th>
                <th
                  colspan="5"
                  class="tw-border-0 tw-border-b tw-border-solid tw-border-gray-400"
                >
                  {{
                    $t("transactionsListCount", { count: transitions.length })
                  }}
                </th>
              </tr>
              <TransactionListRow
                v-for="transaction in transitions"
                :key="transaction.id"
                :transaction="transaction"
                :is-checked="checkedRows.includes(transaction.id)"
                @checkboxUpdate="onTransactionListRowUpdate(transaction.id)"
              />
            </table>
            <div v-else class="tw-text-center custom-py-20">
              {{ $t('emptyListToday') }}
            </div>
          </div>
        </div>
        <div v-else class="tw-text-center custom-py-20">
          {{ $t('emptyList') }}
        </div>
      </div>
    <!-- </transition> -->

    <Modal v-if="open" @close="open = false">
      <AddTransaction :defaultCategoryType="categoryType" />
    </Modal>

    <Modal v-if="openMove" @close="openMove = false">
      <TransactionMove :ids="checkedRows" @success="checkedRows = []" />
    </Modal>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import TransactionListRow from '@/components/TransactionListRow'
import Modal from '@/components/Modal'
import NumPages from '@/components/NumPages'
import AddTransaction from '@/components/AddTransaction'
import TransactionMove from '@/components/TransactionMove'
import ExportButton from '@/components/ExportButton'

export default {
  data () {
    return {
      open: false,
      categoryType: '',
      openMove: false,
      checkedRows: [],
      featurePeriod: 7,
      upcomingBlockOpened: false
    }
  },

  components: {
    TransactionListRow,
    Modal,
    NumPages,
    AddTransaction,
    TransactionMove,
    ExportButton
  },

  computed: {
    ...mapState('transaction', ['transactions', 'queryParams', 'loading', 'detailsInterval']),
    ...mapGetters({
      currentType: 'getCurrentType'
    }),

    isShowFuture () {
      const today = this.$moment().format('YYYY-MM-DD')
      return (this.$moment(this.queryParams.to).diff(today, 'days') > 0)
    },

    isDetailsMode () {
      return this.detailsInterval.from !== '' || this.detailsInterval.to !== ''
    },

    sortedTransactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const result = this.transactions.data.reduce((arr, t) => {
        const istart = this.$moment(t.date)
        if (istart.diff(today, 'days') > 0) {
          arr.upcoming.push(t)
        } else {
          arr.incoming.push(t)
        }
        return arr
      }, {
        incoming: [],
        upcoming: []
      })
      return result
    },

    isTransactionsExists () {
      return this.sortedTransactions.incoming.length || this.sortedTransactions.upcoming.length
    },

    incomingTransactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const acc = this.transactions.offset === 0 && !this.isDetailsMode ? { today: [] } : {}
      const result = this.sortedTransactions.incoming.reduce((acc, e) => {
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
    },

    upcomingTransactions () {
      const today = this.$moment().format('YYYY-MM-DD')
      const result = this.sortedTransactions.upcoming.reduce((arr, t) => {
        const istart = this.$moment(t.date)
        if (istart.diff(today, 'days') <= this.featurePeriod) {
          arr.push(t)
        }
        return arr
      }, [])
      return result
    }

  },

  created () {
    this.featurePeriod = +localStorage.getItem('upcoming_transactions_days') || this.featurePeriod
    this.upcomingBlockOpened = +localStorage.getItem('upcoming_transactions_show') || this.upcomingBlockOpened
  },

  methods: {
    addTransaction (type) {
      this.open = true
      this.categoryType = type
    },

    checkAll ({ target }) {
      this.checkedRows = target.checked ? this.transactions.data.map((r) => r.id) : []
    },

    onTransactionListRowUpdate (id) {
      const index = this.checkedRows.indexOf(id)
      if (index > -1) {
        this.checkedRows.splice(index, 1)
      } else {
        this.checkedRows.push(id)
      }
    },

    bulkDelete () {
      if (confirm(this.$t('bulkDeleteWarning'))) {
        this.$store.dispatch('transaction/bulkDelete', this.checkedRows)
          .then(() => {
            this.checkedRows = []
          })
          .catch(() => {})
      }
    },

    setFeaturePeriod (days) {
      this.featurePeriod = days
      this.upcomingBlockOpened = true
      localStorage.setItem('upcoming_transactions_days', days)
      localStorage.setItem('upcoming_transactions_show', 1)
    },

    toggleupcomingBlockOpened () {
      this.upcomingBlockOpened = !this.upcomingBlockOpened
      localStorage.setItem('upcoming_transactions_show', this.upcomingBlockOpened ? 1 : 0)
    }
  }
}
</script>

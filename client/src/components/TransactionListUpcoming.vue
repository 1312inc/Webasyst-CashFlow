<template>
  <div v-if="showComponent">
    <div v-if="!loading" class="flexbox middle">
      <div v-if="$helper.showMultiSelect()" style="width: 1rem;"></div>
      <div
        @click="toggleupcomingBlockOpened"
        class="tw-cursor-pointer custom-mr-8 black"
        :class="{ 'opacity-50': !upcomingBlockOpened }"
      >
        <i class="fas fa-caret-down"></i>&nbsp;
        <span v-if="featurePeriod === 1">{{ $t("tomorrow") }}</span>
        <span v-else>{{ $t("nextDays", { count: featurePeriod }) }}</span
        >&nbsp;
        <span v-if="filteredTransactions.length"
          >({{ filteredTransactions.length }})</span
        >
      </div>
      <div
        @mouseover="$refs.dropdown.style.display = 'block'"
        @mouseleave="$refs.dropdown.style.display = 'none'"
        class="dropdown tw-z-40"
      >
        <span class="icon"><i class="fas fa-ellipsis-v"></i></span>
        <div class="dropdown-body" ref="dropdown">
          <ul class="menu">
            <li>
              <a href="#" @click.prevent="setFeaturePeriod(1)"
                ><span>{{ $t("tomorrow") }}</span></a
              >
            </li>
            <li>
              <a href="#" @click.prevent="setFeaturePeriod(7)"
                ><span>{{ $t("nextDays", { count: 7 }) }}</span></a
              >
            </li>
            <li>
              <a href="#" @click.prevent="setFeaturePeriod(30)"
                ><span>{{ $t("nextDays", { count: 30 }) }}</span></a
              >
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div v-if="upcomingBlockOpened" class="custom-mt-24">
      <div v-if="!loading">
        <div class="align-right">
          <ExportButton type="upcoming" />
        </div>
        <TransactionListGroup :group="filteredTransactions" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import api from '@/plugins/api'
import transactionListMixin from '@/mixins/transactionListMixin'
import TransactionListGroup from '@/components/TransactionListGroup'
import ExportButton from '@/components/ExportButton'
export default {
  mixins: [transactionListMixin],

  data () {
    return {
      loading: true,
      transactions: [],
      featurePeriod: 7,
      upcomingBlockOpened: false
    }
  },

  components: {
    TransactionListGroup,
    ExportButton
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    showComponent () {
      return (
        this.queryParams.to >=
        this.$moment()
          .add(1, 'M')
          .format('YYYY-MM-DD')
      )
    },

    filteredTransactions () {
      const today = this.$moment()
        .add(1, 'd')
        .format('YYYY-MM-DD')
      const result = this.transactions.filter(t => {
        const istart = this.$moment(t.date)
        return istart.diff(today, 'days') <= this.featurePeriod
      })
      return result
    }
  },

  created () {
    this.featurePeriod =
      +localStorage.getItem('upcoming_transactions_days') || this.featurePeriod
    this.upcomingBlockOpened =
      +localStorage.getItem('upcoming_transactions_show') ||
      this.upcomingBlockOpened
  },

  methods: {
    async getTransactions () {
      if (!this.showComponent) return

      this.loading = true
      const params = { ...this.queryParams }
      params.from = this.$moment()
        .add(1, 'd')
        .format('YYYY-MM-DD')
      params.to = this.$moment()
        .add(1, 'M')
        .format('YYYY-MM-DD')
      if (
        this.detailsInterval.from &&
        this.detailsInterval.from > params.from
      ) {
        params.from = this.detailsInterval.from
      }
      if (this.detailsInterval.to) params.to = this.detailsInterval.to
      const { data } = await api.get('cash.transaction.getList', {
        params
      })

      this.transactions = data.data
      this.loading = false
    },

    setFeaturePeriod (days) {
      this.featurePeriod = days
      this.upcomingBlockOpened = true
      localStorage.setItem('upcoming_transactions_days', days)
      localStorage.setItem('upcoming_transactions_show', 1)
    },

    toggleupcomingBlockOpened () {
      this.upcomingBlockOpened = !this.upcomingBlockOpened
      localStorage.setItem(
        'upcoming_transactions_show',
        this.upcomingBlockOpened ? 1 : 0
      )
    }
  }
}
</script>

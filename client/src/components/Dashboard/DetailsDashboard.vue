<template>
  <div class="custom-px-24 custom-py-16 custom-ml-4 custom-p-12-mobile custom-m-0-mobile">
    <div
      v-if="isFetching"
      class="skeleton flexbox vertical space-24"
    >
      <div
        v-for="i in 4"
        :key="i"
        :class="{'width-80': i === 1}"
      >
        <span
          class="skeleton-line custom-m-0"
          style="height: 40px;"
        />
      </div>
    </div>
    <template v-else>
      <div v-if="data.length">
        <div class="flexbox custom-mb-24 space-12 wrap-mobile">
          <div class="wide custom-mb-0 custom-mb-8-mobile">
            <div class="flexbox space-8 middle">
              <div class="larger black bold">
                <div v-if="showCustomDates">
                  {{ dates }}
                </div>
                <div v-else>
                  {{ rangeDisplayLabel }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="isPastMode"
            class="flexbox space-8 wrap-mobile"
          >
            <button
              class="button light-gray"
              @click="openModal = true"
            >
              {{ $t("setDates") }}
            </button>
            <ExportButton
              v-if="$appState.isDesktop"
            />
          </div>
        </div>

        <DetailsDashboardItem
          v-if="dashboardData"
          :item-data="dashboardData"
        />

        <portal v-if="openModal">
          <Modal @close="openModal = false">
            <UpdateDetailsInterval @close="openModal = false" />
          </Modal>
        </portal>
      </div>
      <DetailsDashboardEmpty v-else-if="isPastMode && $appState.isDesktop" />
    </template>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { mapGetters, mapState } from 'vuex'
import Modal from '@/components/Modal'
import DetailsDashboardItem from './DetailsDashboardItem.vue'
import UpdateDetailsInterval from '@/components/Modals/UpdateDetailsInterval'
import ExportButton from '@/components/Buttons/ExportButton'
import { getIntervalFromLabel } from '@/utils/getDateFromLocalStorage'
import { clearBreakdownTotals, setBreakdownTotals } from '@/utils/breakdownTotals'
import { defineAsyncComponent } from 'vue'

export default {
  components: {
    Modal,
    DetailsDashboardItem,
    UpdateDetailsInterval,
    ExportButton,
    DetailsDashboardEmpty: defineAsyncComponent(() => import('@/components/ContentBlocks/DetailsDashboardEmpty.vue'))
  },

  props: {
    mode: {
      type: String,
      default: 'from',
      validator: value => value === 'from' || value === 'to'
    }
  },

  data () {
    return {
      data: [],
      openModal: false,
      isFetching: true,
      fetchId: 0
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval', 'chartInterval']),
    ...mapGetters('transaction', ['isDetailsMode']),

    isPastMode () {
      return this.mode === 'from'
    },

    showCustomDates () {
      return this.isPastMode && this.isDetailsMode
    },

    dates () {
      return this.detailsInterval.from !== this.detailsInterval.to
        ? `${this.$moment(
            this.detailsInterval.from
          ).format('LL')} – ${this.$moment(
            this.detailsInterval.to
          ).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}`
    },

    dashboardData () {
      return this.data.find(
        i =>
          i.currency === this.$store.getters['transaction/activeCurrencyCode']
      )
    },

    rangeDisplayLabel () {
      const type = this.isPastMode ? 'from' : 'to'
      const intervalBound = this.chartInterval[type]
      return intervalBound ? this.$t(getIntervalFromLabel(type)) : ''
    },

    today () {
      return this.$moment().format('YYYY-MM-DD')
    },

    tomorrow () {
      return this.$moment().add(1, 'day').format('YYYY-MM-DD')
    },

    breakdownWatchKey () {
      const filter = this.queryParams.filter

      if (this.showCustomDates) {
        return `custom|${this.detailsInterval.from}|${this.detailsInterval.to}|${filter}`
      }
      if (this.isPastMode) {
        return `from|${this.chartInterval.from}|${this.today}|${filter}`
      }
      return `to|${this.tomorrow}|${this.chartInterval.to}|${filter}`
    }
  },

  created () {
    this.unsubscribeFromMutations = this.$store.subscribe(({ type }) => {
      switch (type) {
        case 'transaction/updateTransactions':
        case 'transaction/deleteTransaction':
        case 'transaction/createTransactions':
          this.fetchBreakDown()
      }
    })

    this.unsubscribeFromActions = this.$store.subscribeAction({
      after: ({ type }) => {
        if (type === 'transactionBulk/bulkMove') {
          this.fetchBreakDown()
        }
      }
    })
  },

  watch: {
    dashboardData: {
      handler (item) {
        setBreakdownTotals(this.mode, item)
      },
      immediate: true
    },

    breakdownWatchKey: {
      handler () {
        this.fetchBreakDown()
      },
      immediate: true
    }
  },

  beforeDestroy () {
    this.unsubscribeFromMutations()
    this.unsubscribeFromActions()
    clearBreakdownTotals(this.mode)
  },

  methods: {
    fetchBreakDown () {
      let from
      let to

      if (this.showCustomDates) {
        from = this.detailsInterval.from
        to = this.detailsInterval.to
      } else if (this.isPastMode) {
        from = this.chartInterval.from
        to = this.today
      } else {
        from = this.tomorrow
        to = this.chartInterval.to
        if (to < from) {
          this.data = []
          this.isFetching = false
          return
        }
      }

      const requestId = ++this.fetchId
      this.isFetching = true
      api
        .get('cash.aggregate.getBreakDown', {
          params: {
            from,
            to,
            filter: this.queryParams.filter
          }
        })
        .then(({ data }) => {
          if (requestId !== this.fetchId) return
          this.data = data
        })
        .finally(() => {
          if (requestId !== this.fetchId) return
          this.isFetching = false
        })
    }

  }
}
</script>

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
                <div v-if="!isDefaultRange">
                  {{ dates }}
                </div>
                <div v-else>
                  {{ rangeDisplayLabel }}
                </div>
              </div>

              <DropdownWaFloating>
                <template #toggler>
                  <button class="circle light-gray">
                    <span class="icon"><i class="fas fa-ellipsis-v" /></span>
                  </button>
                </template>
                <ul class="menu custom-mb-0">
                  <li>
                    <a @click.prevent="setPeriod('from')"><span>{{ $t(rangeLabelFrom) }}</span></a>
                  </li>
                  <li>
                    <a @click.prevent="setPeriod('to')"><span>{{ $t(rangeLabelTo) }}</span></a>
                  </li>
                </ul>
                <hr class="custom-m-0">
                <ul class="menu custom-mt-0">
                  <li>
                    <a @click.prevent="setLocalBreakdownPeriod(7)"><span>{{ $t("nextDays", { count: 7 }) }}</span></a>
                  </li>
                  <li>
                    <a @click.prevent="setLocalBreakdownPeriod(30)"><span>{{ $t("nextDays", { count: 30 }) }}</span></a>
                  </li>
                  <li>
                    <a @click.prevent="setLocalBreakdownPeriod(90)"><span>{{ $t("nextDays", { count: 90 }) }}</span></a>
                  </li>
                  <li>
                    <a @click.prevent="setLocalBreakdownPeriod(180)"><span>{{ $t("nextDays", { count: 180 }) }}</span></a>
                  </li>
                </ul>
              </DropdownWaFloating>
            </div>
          </div>

          <div class="flexbox space-8 wrap-mobile">
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
      <DetailsDashboardEmpty v-else-if="$appState.isDesktop" />
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
import {
  clearLocalBreakdownToDays,
  getLocalBreakdownToDays,
  setLocalBreakdownToDays
} from '@/utils/breakdownLocalPeriod'
import DropdownWaFloating from '../Inputs/DropdownWaFloating.vue'
import { defineAsyncComponent } from 'vue'

const CURRENT_PERIOD_STORAGE_KEY = 'currentPeriod'

function readCurrentPeriodFromStorage () {
  try {
    const v = localStorage.getItem(CURRENT_PERIOD_STORAGE_KEY)
    return v === 'from' || v === 'to' ? v : 'to'
  } catch (_) {
    return 'to'
  }
}

export default {
  components: {
    Modal,
    DetailsDashboardItem,
    UpdateDetailsInterval,
    ExportButton,
    DetailsDashboardEmpty: defineAsyncComponent(() => import('@/components/ContentBlocks/DetailsDashboardEmpty.vue')),
    DropdownWaFloating
  },

  data () {
    return {
      data: [],
      openModal: false,
      isFetching: false,
      rangeLabelFrom: '',
      rangeLabelTo: '',
      dashboardCurrentPeriod: readCurrentPeriodFromStorage(),
      suppressLocalBreakdownClear: false,
      localBreakdownToDays: getLocalBreakdownToDays()
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval', 'chartInterval']),
    ...mapGetters('transaction', ['isDetailsMode']),

    isDefaultRange () {
      return !this.isDetailsMode
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

    currentPeriod: {
      get () {
        return this.dashboardCurrentPeriod
      },
      set (value) {
        if (value !== 'from' && value !== 'to') return
        this.dashboardCurrentPeriod = value
        try {
          localStorage.setItem(CURRENT_PERIOD_STORAGE_KEY, value)
        } catch (_) {}
      }
    },

    breakdownIntervalKey () {
      const { from, to } = this.detailsInterval
      return `${from}|${to}`
    },

    breakdownWatchKey () {
      return `${this.breakdownIntervalKey}|${this.queryParams.filter}`
    },

    rangeDisplayLabel () {
      if (this.currentPeriod === 'from') {
        return this.$t(this.rangeLabelFrom)
      }
      if (this.localBreakdownToDays != null && this.isDefaultRange) {
        return this.$t('nextDays', { count: this.localBreakdownToDays })
      }
      return this.$t(this.rangeLabelTo)
    }
  },

  watch: {
    breakdownIntervalKey (newKey, oldKey) {
      if (this.suppressLocalBreakdownClear) return
      if (oldKey != null && newKey !== oldKey) {
        clearLocalBreakdownToDays()
        this.localBreakdownToDays = null
      }
    },

    breakdownWatchKey: {
      handler () {
        this.fetchBreakDown()
      },
      immediate: true
    }
  },

  methods: {
    fetchBreakDown () {
      this.rangeLabelFrom = getIntervalFromLabel('from')
      this.rangeLabelTo = getIntervalFromLabel('to')
      this.localBreakdownToDays = getLocalBreakdownToDays()

      const currentDate = this.$moment().format('YYYY-MM-DD')
      const localToDays = this.localBreakdownToDays

      let from
      let to

      if (!this.isDefaultRange) {
        from = this.detailsInterval.from
        to = this.detailsInterval.to
      } else if (localToDays != null) {
        from = currentDate
        to = this.$moment().add(localToDays, 'days').format('YYYY-MM-DD')
      } else {
        from = this.currentPeriod === 'from' ? this.detailsInterval.from : currentDate
        to = this.currentPeriod === 'to' ? this.detailsInterval.to : currentDate
      }

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
          this.data = data
        })
        .finally(() => {
          this.isFetching = false
        })
    },

    setPeriod (period) {
      clearLocalBreakdownToDays()
      this.localBreakdownToDays = null
      this.currentPeriod = period
      if (!this.isDefaultRange) {
        this.$store.dispatch('transaction/resetDetailsInterval')
      } else {
        this.fetchBreakDown()
      }
    },

    setLocalBreakdownPeriod (days) {
      setLocalBreakdownToDays(days)
      this.localBreakdownToDays = days
      this.currentPeriod = 'to'
      if (!this.isDefaultRange) {
        this.suppressLocalBreakdownClear = true
        this.$store.dispatch('transaction/resetDetailsInterval')
        this.$nextTick(() => {
          this.suppressLocalBreakdownClear = false
        })
        return
      }
      this.fetchBreakDown()
    }

  }
}
</script>

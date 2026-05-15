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
                  {{ $t(currentPeriod === 'from' ? rangeLabelFrom : rangeLabelTo) }}
                </div>
              </div>

              <DropdownWaFloating>
                <template #toggler>
                  <button class="circle light-gray">
                    <span class="icon"><i class="fas fa-ellipsis-v" /></span>
                  </button>
                </template>
                <ul class="menu">
                  <li>
                    <a @click.prevent="setPeriod('from')"><span>{{ $t(rangeLabelFrom) }}</span></a>
                  </li>
                  <li>
                    <a @click.prevent="setPeriod('to')"><span>{{ $t(rangeLabelTo) }}</span></a>
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
              v-if="!appState.webView"
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
      <DetailsDashboardEmpty v-else />
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
import { appState } from '@/utils/appState'
import { getIntervalFromLabel } from '@/utils/getDateFromLocalStorage'
import DetailsDashboardEmpty from '../ContentBlocks/DetailsDashboardEmpty.vue'
import DropdownWaFloating from '../Inputs/DropdownWaFloating.vue'

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
    DetailsDashboardEmpty,
    DropdownWaFloating
  },

  data () {
    return {
      data: [],
      openModal: false,
      appState,
      isFetching: false,
      rangeLabelFrom: '',
      rangeLabelTo: '',
      dashboardCurrentPeriod: readCurrentPeriodFromStorage()
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
    }
  },

  watch: {
    detailsInterval: {
      handler () { this.fetchBreakDown() },
      immediate: true
    },
    'queryParams.filter': 'fetchBreakDown',
    currentPeriod: 'fetchBreakDown'
  },

  methods: {
    fetchBreakDown () {
      this.rangeLabelFrom = getIntervalFromLabel('from')
      this.rangeLabelTo = getIntervalFromLabel('to')

      const today = new Date()
      const currentDate = today.toISOString().split('T')[0]

      const from = this.currentPeriod === 'from' ? this.detailsInterval.from : currentDate
      const to = this.currentPeriod === 'to' ? this.detailsInterval.to : currentDate

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
      this.currentPeriod = period
    }

  }
}
</script>

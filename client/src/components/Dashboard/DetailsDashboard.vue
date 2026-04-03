<template>
  <BlankBox
    v-if="data && dashboardData"
    class="custom-p-24"
  >
    <div class="flexbox custom-mb-24">
      <div class="wide flexbox middle wrap-mobile">
        <div class="larger black bold custom-mb-0 custom-mb-8-mobile">
          <div v-if="!isDefaultRange">
            {{ dates }}
          </div>
          <div v-else>
            {{ $t(rangeLabel) }}
          </div>
        </div>
        <button
          class="button light-gray custom-ml-12 custom-ml-0-mobile"
          @click="openModal = true"
        >
          {{ $t("setDates") }}
        </button>
        <ExportButton
          v-if="!appState.webView"
          class="custom-ml-12 custom-ml-8-mobile"
        />
      </div>
      <!-- <div>
        <button
          class="nobutton largest custom-p-0"
          @click="closeDashboard"
        >
          <i class="fas fa-times gray" />
        </button>
      </div> -->
    </div>

    <DetailsDashboardItem
      v-if="dashboardData"
      :item-data="dashboardData"
    />

    <portal>
      <Modal v-if="openModal">
        <UpdateDetailsInterval @close="openModal = false" />
      </Modal>
    </portal>
  </BlankBox>
</template>

<script>
import api from '@/plugins/api'
import { mapState } from 'vuex'
import Modal from '@/components/Modal'
import DetailsDashboardItem from './DetailsDashboardItem.vue'
import UpdateDetailsInterval from '@/components/Modals/UpdateDetailsInterval'
import ExportButton from '@/components/Buttons/ExportButton'
import { appState } from '@/utils/appState'
import BlankBox from '../BlankBox.vue'
import { getIntervalFromLabel } from '@/utils/getDateFromLocalStorage'

export default {
  components: {
    Modal,
    DetailsDashboardItem,
    UpdateDetailsInterval,
    ExportButton,
    BlankBox
  },

  data () {
    return {
      data: null,
      openModal: false,
      appState,
      isFetching: false,
      rangeLabel: ''
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval', 'chartInterval']),

    isDefaultRange () {
      return this.detailsInterval.from === this.chartInterval.from && this.detailsInterval.to === this.chartInterval.to
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
    }
  },

  watch: {
    detailsInterval: 'fetchBreakDown',
    'queryParams.filter': 'fetchBreakDown'
  },

  mounted () {
    this.$store.commit('transaction/setDetailsInterval', {
      from: this.chartInterval.from, to: this.chartInterval.to
    })
  },

  methods: {
    fetchBreakDown () {
      this.rangeLabel = getIntervalFromLabel('from')

      const from = this.detailsInterval.from
      const to = this.detailsInterval.to

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
    }

    // closeDashboard () {
    //   this.$store.dispatch('transaction/updateDetailsInterval', {
    //     from: '',
    //     to: ''
    //   })
    // }
  }
}
</script>

<template>
  <BlankBox v-if="data && dashboardData">
    <div class="flexbox custom-mb-24">
      <div class="wide flexbox middle wrap-mobile">
        <div
          class="larger black bold custom-mb-0 custom-mb-8-mobile"
          v-html="dates"
        />
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
      <div>
        <button
          class="nobutton largest custom-p-0"
          @click="closeDashboard"
        >
          <i class="fas fa-times gray" />
        </button>
      </div>
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
      appState
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval', 'chartInterval']),

    dates () {
      return this.detailsInterval.from !== this.detailsInterval.to
        ? `<span class="nowrap">${this.$moment(
            this.detailsInterval.from
          ).format('LL')}</span> – <span class="nowrap">${this.$moment(
            this.detailsInterval.to
          ).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}</span>`
    },

    dashboardData () {
      return this.data.find(
        i =>
          i.currency === this.$store.getters['transaction/activeCurrencyCode']
      )
    }
  },

  watch: {
    detailsInterval: 'fetchBreakDown'
  },

  mounted () {
    this.$store.commit('transaction/setDetailsInterval', {
      from: this.chartInterval.from, to: this.chartInterval.to
    })
  },

  methods: {
    fetchBreakDown (val) {
      if (val.from && val.to) {
        api
          .get('cash.aggregate.getBreakDown', {
            params: {
              from: val.from,
              to: val.to,
              filter: this.queryParams.filter
            }
          })
          .then(({ data }) => {
            this.data = data
          })
      } else {
        this.data = null
      }
    },

    closeDashboard () {
      this.$store.dispatch('transaction/updateDetailsInterval', {
        from: '',
        to: ''
      })
    }
  }
}
</script>

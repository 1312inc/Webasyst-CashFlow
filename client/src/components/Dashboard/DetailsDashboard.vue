<template>
  <div
    v-if="data"
    class="c-breakdown-details"
  >
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
        <ExportButton class="custom-ml-12 custom-ml-8-mobile" />
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

    <DetailsDashboardItem :item-data="dashboardData" />

    <portal>
      <Modal v-if="openModal">
        <UpdateDetailsInterval @close="openModal = false" />
      </Modal>
    </portal>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { mapState } from 'vuex'
import Modal from '@/components/Modal'
import DetailsDashboardItem from './DetailsDashboardItem.vue'
import UpdateDetailsInterval from '@/components/Modals/UpdateDetailsInterval'
import ExportButton from '@/components/Buttons/ExportButton'

export default {
  components: {
    Modal,
    DetailsDashboardItem,
    UpdateDetailsInterval,
    ExportButton
  },

  data () {
    return {
      data: null,
      openModal: false
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

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

<style lang="scss">
.c-breakdown-details {
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1),
    0 0.5rem 1.5rem -0.5rem rgba(0, 0, 0, 0.2);
  border-radius: 0.375rem;
  padding: 1rem 1.2rem;
  margin: 1.7rem;

  @include for-mobile {
    padding: 1rem;
    margin: 1rem;
  }
}
</style>

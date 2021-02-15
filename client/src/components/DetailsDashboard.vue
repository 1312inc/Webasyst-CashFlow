<template>
  <div v-if="data" class="custom-mb-24 c-breakdown-details">
    <div class="flexbox middle custom-mb-24">
      <div class="wide flexbox middle">
        <h3 class="custom-mb-0">
          {{ dates }}
        </h3>
        <button @click="openModal = true" class="small nobutton">
          {{ $t("setDates") }}
        </button>
      </div>
      <div>
        <button @click="closeDashboard" class="small nobutton larger gray">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <div v-for="currency in data" :key="currency.currency" class="flexbox">
      <div class="width-40">
        <div class="">
          <div class="smaller uppercase">{{ $t("income") }}</div>
          <div class="larger text-green">
            {{
              $helper.toCurrency({
                value: currency.income.totalAmount,
                currencyCode: currency.currency,
                isDynamics: true,
              })
            }}
          </div>
        </div>
        <ChartPie :data="currency.income.data" :currency="currency.currency" />
        <AmChartLegend
          :legendItems="currency.income.data"
          :currencyCode="currency.currency"
        />
      </div>

      <div class="width-40">
        <div class="">
          <div class="smaller uppercase">{{ $t("expense") }}</div>
          <div class="larger text-red">
            {{
              $helper.toCurrency({
                value: currency.expense.totalAmount,
                currencyCode: currency.currency,
                isDynamics: true,
                isReverse: true,
              })
            }}
          </div>
        </div>
        <ChartPie :data="currency.expense.data" :currency="currency.currency" />
        <AmChartLegend
          :legendItems="currency.expense.data"
          :currencyCode="currency.currency"
        />
      </div>

      <div>
        <div class="smaller uppercase">{{ $t("balance") }}</div>
        <div class="larger">
          {{
            $helper.toCurrency({
              value: currency.income.totalAmount - currency.expense.totalAmount,
              currencyCode: currency.currency,
            })
          }}
        </div>
      </div>
    </div>

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
import ChartPie from '@/components/AmChartPie'
import AmChartLegend from '@/components/AmChartLegend'
import UpdateDetailsInterval from '@/components/Modals/UpdateDetailsInterval'

export default {
  components: {
    Modal,
    ChartPie,
    AmChartLegend,
    UpdateDetailsInterval
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
        ? `${this.$moment(this.detailsInterval.from).format('LL')} – ${this.$moment(
            this.detailsInterval.to
          ).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}`
    }
  },

  watch: {
    detailsInterval: 'fetchBreakDown'
  },

  methods: {
    fetchBreakDown (val) {
      if (val.from && val.to) {
        api.get('cash.aggregate.getBreakDown', {
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
        to: '',
        outOfChart: true
      })
    }
  }
}
</script>

<style lang="scss">
.c-breakdown-details {
  border: 1.5px solid var(--alert-border-color);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05),
    0 0.5rem 0.5rem -0.5rem rgba(0, 0, 0, 0.13);
  border-radius: 0.25rem;
  padding: 1.5rem;
}
</style>
